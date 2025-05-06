<?php

namespace App\Controller;

use App\Lib\WPPasswordHasher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HashController extends AbstractController
{
    #[Route('/hash', name: 'app_hash')]
    public function index(): Response
    {
        return $this->render('hash/index.html.twig', [
            'controller_name' => 'HashController',
            'title' => 'Hash',
        ]);
    }

    #[Route('/api/hash/encode', name: 'api_hash_encode', methods: ['POST'])]
    public function apiHashEncode(Request $request): JsonResponse
    {
        try {
            // Get data from the request body
            $content = $request->getContent();

            $payload = json_decode($content,true);

            $supported = hash_algos();

            if( ! in_array($payload['alghorithm'], $supported)){
                return new JsonResponse(['error' => 'Invalid alghorithm'], 400);
            }

            $str = hash($payload['alghorithm'], $payload['content']);

            return new JsonResponse(['success' => true, 'formatted' => $str], 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid input', 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/hash/wp_pass', name: 'api_hash_wp_pass', methods: ['POST'])]
    public function apiHashWpPass(Request $request): JsonResponse
    {
        try {
            // Get data from the request body
            $content = $request->getContent();


            $hasher = new WPPasswordHasher();

            $hash = $hasher->hashPassword($content);
            

            return new JsonResponse(['success' => true, 'formatted' => $hash], 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid input', 'message' => $e->getMessage()], 400);
        }
    }

}
