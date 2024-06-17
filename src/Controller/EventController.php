<?php

namespace App\Controller;

use App\Service\CalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/events')]

class EventController extends AbstractController
{
    public function __construct(private CalendarService $calendarService) {}

    #[Route('', name: 'createEvent', methods: ['POST'])]
    public function createEvent(Request $request): JsonResponse
    {
        return $this->calendarService->createEvent($request->getContent());
    }

    #[Route('', name: 'getEvents', methods: ['GET'])]
    public function getEvents(): JsonResponse
    {
        return $this->calendarService->getEvents();
    }

    #[Route('/{id}', name: 'updateEvent', methods: ['PUT'])]
    public function updateEvent(int $id, Request $request): JsonResponse
    {
        return $this->calendarService->updateEvent($id, $request->getContent());
    }

    #[Route('/{id}', name: 'deleteEvent', methods: ['DELETE'])]
    public function deleteEvent(int $id): JsonResponse
    {
        return $this->calendarService->deleteEvent($id);
    }
}
