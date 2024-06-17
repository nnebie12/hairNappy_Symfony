<?php


namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/api/signup', name: 'signup', methods: ['POST'])]
    public function signup(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        return $this->userService->signup($data);
    }

    #[Route('/api/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        return $this->userService->login($data);
    }

    #[Route('/api/me', name: 'getUserDetails', methods: ['GET'])]
    public function getUserDetails(): JsonResponse
    {
        return $this->userService->getUserDetails();
    }

    #[Route('/api/me', name: 'updateUser', methods: ['PUT'])]
    public function updateUser(Request $request): JsonResponse
    {
        return $this->userService->updateUser($request);
    }

    #[Route('/api/me', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(): JsonResponse
    {
        return $this->userService->deleteUser();
    }
}
