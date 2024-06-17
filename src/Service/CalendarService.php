<?php


namespace App\Service;

use App\Entity\Event;
use App\Form\EventFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormFactoryInterface;

class CalendarService
{
    public function __construct(private EntityManagerInterface $entityManager, private Security $security,
                                private FormFactoryInterface $formFactory, private NotificationService $notificationService,
                                private string $smsForNewEvent, private string $smsRecipient) {}

    public function createEvent(string $content): JsonResponse
    {
        $form = $this->formFactory->create(EventFormType::class, (new Event()));
        $form->submit(json_decode($content, true));

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->security->getUser();
            $event = $form->getData();
            $event->setUser($user);
            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $additionalInformation = " | Salon : {$event->getSalon()->getName()}, Date : {$event->getDate()->format('d/m/Y')} à {$event->getHeure()->format('H\hi')}, Complément d'informations : {$event->getMessage()} ";
            $message = sprintf($this->smsForNewEvent, $additionalInformation);
            $this->notificationService->notify($this->smsRecipient, $message);
            return new JsonResponse(['status' => 'success', 'message' => 'Event created!'], JsonResponse::HTTP_CREATED);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return new JsonResponse(['status' => 'error', 'errors' => $errors], JsonResponse::HTTP_BAD_REQUEST);
    }

    public function getEvents(): JsonResponse
    {
        $user = $this->security->getUser();
        $events = $this->entityManager->getRepository(Event::class)->findBy(['user' => $user]);
        $data = [];
        foreach ($events as $event) {
            $data[] = [
                'id' => $event->getId(),
                'date' => $event->getDate() ? $event->getDate()->format('d/m/Y') : null,
                'heure' => $event->getHeure() ? $event->getHeure()->format('H:i') : null,
                'message' => $event->getMessage(),
                'user' => $event->getUser()->getId(),
                'salon' => $event->getSalon()->getName()
            ];
        }
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    public function updateEvent(int $id, string $content): JsonResponse
    {
        $data = json_decode($content, true);
        $user = $this->security->getUser();

        $event = $this->entityManager->getRepository(Event::class)->findOneBy(['id' => $id, 'user' => $user]);

        if (!$event) {
            return new JsonResponse(['status' => 'error', 'message' => 'Event not found or you are not authorized to update this event'], JsonResponse::HTTP_NOT_FOUND);
        }

        $form = $this->formFactory->create(EventFormType::class, $event);
        $form->submit($data, false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return new JsonResponse(['status' => 'success', 'message' => 'Event updated'], JsonResponse::HTTP_OK);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse(['status' => 'error', 'errors' => $errors], JsonResponse::HTTP_BAD_REQUEST);
    }

    public function deleteEvent(int $id): JsonResponse
    {
        $user = $this->security->getUser();

        $event = $this->entityManager->getRepository(Event::class)->findOneBy(['id' => $id, 'user' => $user]);

        if (!$event) {
            return new JsonResponse(['status' => 'error', 'message' => 'Event not found or you are not authorized to delete this event'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();
        return new JsonResponse(['status' => 'success', 'message' => 'Event deleted'], JsonResponse::HTTP_OK);
    }
}
