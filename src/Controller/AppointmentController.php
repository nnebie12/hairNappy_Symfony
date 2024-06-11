<?php
// src/Controller/AppointmentController.php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppointmentController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/appointment', name: 'api_create_appointment', methods: ['POST'])]
    public function createAppointment(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $appointment = new Appointment();
        $form = $this->createForm(AppointmentFormType::class, $appointment);
        $form->submit($data);

        // Log the heure data to ensure it's being processed correctly
        $heure = $appointment->getHeure();
        error_log('Heure data: ' . ($heure ? $heure->format('H:i') : 'null'));

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser(); 
            if (!$user) {
                return $this->createCorsResponse(new JsonResponse(['status' => 'error', 'message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED));
            }

            $appointment->setUser($user);
            $this->entityManager->persist($appointment);
            $this->entityManager->flush();

            return $this->createCorsResponse(new JsonResponse(['status' => 'success', 'message' => 'Appointment created!'], Response::HTTP_CREATED));
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->createCorsResponse(new JsonResponse(['status' => 'error', 'errors' => $errors], Response::HTTP_BAD_REQUEST));
    }

    #[Route('/api/appointments', name: 'api_my_appointments', methods: ['GET'])]
    public function getMyAppointments(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $appointments = $this->entityManager->getRepository(Appointment::class)->findBy(['user' => $user]);

        // Manually format the data
        $data = [];
        foreach ($appointments as $appointment) {
            $data[] = [
                'id' => $appointment->getId(),
                'date' => $appointment->getDate(),
                'heure' => $appointment->getHeure() ? $appointment->getHeure()->format('H:i') : null,
                'message' => $appointment->getMessage(),
                'user' => $appointment->getUser()->getId()
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/appointments/{id}', name: 'api_update_appointment', methods: ['PUT'])]
    public function updateAppointment(int $id, Request $request): JsonResponse
    {
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($id);
        if (!$appointment) {
            return $this->createCorsResponse(new JsonResponse(['status' => 'error', 'message' => 'Appointment not found'], Response::HTTP_NOT_FOUND));
        }

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(AppointmentFormType::class, $appointment, ['csrf_protection' => false]);
        $form->submit($data, false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->createCorsResponse(new JsonResponse(['status' => 'success', 'message' => 'Appointment updated']));
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->createCorsResponse(new JsonResponse(['status' => 'error', 'errors' => $errors], Response::HTTP_BAD_REQUEST));
    }

    #[Route('/api/appointments/{id}', name: 'api_delete_appointment', methods: ['DELETE'])]
    public function deleteAppointment(int $id): JsonResponse
    {
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($id);
        if (!$appointment) {
            return $this->createCorsResponse(new JsonResponse(['status' => 'error', 'message' => 'Appointment not found'], Response::HTTP_NOT_FOUND));
        }

        $this->entityManager->remove($appointment);
        $this->entityManager->flush();

        return $this->createCorsResponse(new JsonResponse(['status' => 'success', 'message' => 'Appointment deleted']));
    }

    private function createCorsResponse(JsonResponse $response): JsonResponse
    {
        $response->headers->set('Access-Control-Allow-Origin', 'http://127.0.0.1:5501');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        return $response;
    }
}
