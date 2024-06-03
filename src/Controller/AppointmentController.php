<?php
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

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser(); 
            if (!$user) {
                return new JsonResponse(['status' => 'error', 'message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
            }

            $appointment->setUser($user);
            $this->entityManager->persist($appointment);
            $this->entityManager->flush();

            return new JsonResponse(['status' => 'success', 'message' => 'Appointment created!'], Response::HTTP_CREATED);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse(['status' => 'error', 'errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/api/appointments', name: 'api_get_appointments', methods: ['GET'])]
    public function getAppointments(): JsonResponse
    {
        $appointments = $this->entityManager->getRepository(Appointment::class)->findAll();
        return $this->json($appointments);
    }

    #[Route('/api/appointments/{id}', name: 'api_get_appointment', methods: ['GET'])]
    public function getAppointment(int $id): JsonResponse
    {
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($id);
        if (!$appointment) {
            return new JsonResponse(['status' => 'error', 'message' => 'Appointment not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($appointment);
    }

    #[Route('/api/appointments/{id}', name: 'api_update_appointment', methods: ['PUT'])]
    public function updateAppointment(int $id, Request $request): JsonResponse
    {
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($id);
        if (!$appointment) {
            return new JsonResponse(['status' => 'error', 'message' => 'Appointment not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(AppointmentFormType::class, $appointment, ['csrf_protection' => false]);
        $form->submit($data, false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return new JsonResponse(['status' => 'success', 'message' => 'Appointment updated']);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse(['status' => 'error', 'errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/api/appointments/{id}', name: 'api_delete_appointment', methods: ['DELETE'])]
    public function deleteAppointment(int $id): JsonResponse
    {
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($id);
        if (!$appointment) {
            return new JsonResponse(['status' => 'error', 'message' => 'Appointment not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($appointment);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Appointment deleted']);
    }
}
