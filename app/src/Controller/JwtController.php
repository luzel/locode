<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class JwtController extends AbstractController
{
    #[Route('/jwt', name: 'app_jwt')]
    public function index(): Response
    {
        return $this->render('jwt/index.html.twig', [
            'controller_name' => 'JwtController',
            'title' => 'JWT',
        ]);
    }
}
