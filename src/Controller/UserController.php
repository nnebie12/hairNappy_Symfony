<?php
// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private $userPasswordHasher;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/signup', name: 'api_signup', methods: ['POST'])]
    public function signup(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'], $data['nom'], $data['prenom'], $data['ville'], $data['pays'], $data['numeroDeTelephone'], $data['codePostale'])) {
            return new JsonResponse(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $data['password']));
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setVille($data['ville']);
        $user->setPays($data['pays']);
        $user->setNumeroDeTelephone($data['numeroDeTelephone']);
        $user->setCodePostale($data['codePostale']);
        $user->setDateDeNaissance(new \DateTime($data['dateNaissance']));
    
        if (isset($data['entreprise'])) {
            $user->setEntreprise($data['entreprise']);
        }
    
        if (isset($data['siret'])) {
            $user->setSiret($data['siret']);
        }
    
        if (isset($data['genre'])) {
            $user->setGenre($data['genre']);
        }
    
        if (isset($data['newsletter'])) {
            $user->setNewsletter($data['newsletter'] === 'subscribe');
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'User created!'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UserAuthenticatorInterface $userAuthenticator, FormLoginAuthenticator $authenticator): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['username'], $data['password'])) {
                return new JsonResponse(['error' => 'Missing required fields'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['username']]);

            if (!$user || !$this->userPasswordHasher->isPasswordValid($user, $data['password'])) {
                return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
            }

            
            $userAuthenticator->authenticateUser($user, $authenticator, $request);

            return new JsonResponse(['status' => 'success'], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}