<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QrCodeController extends AbstractController
{
    #[Route('/qr-code', name: 'app_qr_code')]
    public function index(): Response
    {
        return $this->render('qr_code/index.html.twig', [
            'title' => 'QR Code Generator',
        ]);
    }
}
