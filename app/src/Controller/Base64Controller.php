<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class Base64Controller extends AbstractController
{
    #[Route('/base64', name: 'app_base64')]
    public function index(): Response
    {
        return $this->render('base64/index.html.twig', [
            'controller_name' => 'Base64Controller',
            'title' => 'Base64',
        ]);
    }
}
