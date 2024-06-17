<?php

namespace App\Service;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher, private EntityManagerInterface $entityManager, 
                                private JWTTokenManagerInterface $jwtManager, private NotificationService $notificationService ) {}

    public function signup(array $data): JsonResponse
    {
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['status' => 'error', 'errors' => ['Email already exists.']], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user, ['csrf_protection' => false]);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new JsonResponse(['status' => 'User created!'], Response::HTTP_CREATED);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse(['status' => 'error', 'errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    public function login(array $data): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if (!$user || !$this->userPasswordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtManager->create($user);

        return new JsonResponse(['status' => 'success', 'token' => $token], JsonResponse::HTTP_OK);
    }

    public function getUserDetails(): JsonResponse
    {
        $user = $this->getUser();

        $data = [
            'id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'ville' => $user->getVille(),
            'pays' => $user->getPays(),
            'numeroDeTelephone' => $user->getNumeroDeTelephone(),
            'codePostale' => $user->getCodePostale(),
            'entreprise' => $user->getEntreprise(),
            'siret' => $user->getSiret(),
            'genre' => $user->getGenre(),
            'newsletter' => $user->getNewsletter(),
        ];

        return new JsonResponse(['status' => 'success', 'data' => $data], JsonResponse::HTTP_OK);
    }

    public function updateUser(Request $request): JsonResponse
    {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(RegistrationFormType::class, $user, ['csrf_protection' => false]);
        $form->submit($data, false);

        if ($form->isSubmitted() && $form->isValid()) {
            if (isset($data['password']) && !empty($data['password'])) {
                $hashedPassword = $this->userPasswordHasher->hashPassword($user, $data['password']);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->flush();
            return new JsonResponse(['status' => 'success', 'message' => 'User updated']);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse(['status' => 'error', 'errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    public function deleteUser(): JsonResponse
    {
        $user = $this->getUser();

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'User deleted']);
    }

    public function notify(string $phoneNumber, string $message): JsonResponse
    {
        $isSent = $this->notificationService->notify($phoneNumber, $message);

        if ($isSent) {
            return new JsonResponse(['status' => 'success', 'message' => 'Notification sent successfully'], JsonResponse::HTTP_OK);
        } else {
            return new JsonResponse(['status' => 'error', 'message' => 'Failed to send notification'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
