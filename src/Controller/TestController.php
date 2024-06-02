<?php
// src/Controller/TestController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/api/test-cors', name: 'test_cors', methods: ['GET'])]
    public function testCors(): JsonResponse
    {
        return new JsonResponse(['message' => 'CORS is working!']);
    }
}
