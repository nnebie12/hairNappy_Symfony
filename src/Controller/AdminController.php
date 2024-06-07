<?php

// src/Controller/AdminController.php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/index', name: 'admin_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(): JsonResponse
    {
        return new JsonResponse(['message' => 'Welcome to the admin dashboard!']);
    }
}
