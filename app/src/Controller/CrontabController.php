<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CrontabController extends AbstractController
{
    #[Route('/crontab', name: 'app_crontab')]
    public function index(): Response
    {
        return $this->render('crontab/index.html.twig', [
            'controller_name' => 'CrontabController',
            'title' => 'Crontab',
        ]);
    }
}
