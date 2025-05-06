<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TokenGeneratorController extends AbstractController
{
    #[Route('/token/generator', name: 'app_token_generator')]
    public function index(): Response
    {
        return $this->render('token_generator/index.html.twig', [
            'controller_name' => 'TokenGeneratorController',
            'title' => 'Token Generator',
        ]);
    }
}
