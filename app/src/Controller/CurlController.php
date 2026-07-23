<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CurlController extends AbstractController
{
    #[Route('/curl', name: 'app_curl')]
    public function index(): Response
    {
        return $this->render('curl/index.html.twig', [
            'controller_name' => 'CurlController',
            'title' => 'Curl',
        ]);
    }
}
