<?php

// src/Controller/AppointmentController.php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AppointmentController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/appointment', name: 'api_create_appointment', methods: ['POST'])]
    public function createAppointment(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in to create an appointment.');
        }

        $data = json_decode($request->getContent(), true);

        $appointment = new Appointment();
        $form = $this->createForm(AppointmentFormType::class, $appointment);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($appointment);
            $this->entityManager->flush();

            return new JsonResponse(['status' => 'success', 'message' => 'Appointment created'], JsonResponse::HTTP_CREATED);
        }

        return new JsonResponse([
            'status' => 'error',
            'message' => 'Invalid data',
            'errors' => (string) $form->getErrors(true, false),
        ], JsonResponse::HTTP_BAD_REQUEST);
    }
}
