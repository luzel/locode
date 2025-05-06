<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PhpController extends AbstractController
{
    #[Route('/php', name: 'app_php')]
    public function index(): Response
    {
        return $this->render('php/index.html.twig', [
            'controller_name' => 'PhpController',
            'title' => 'PHP',
        ]);
    }

    #[Route('/php/serialize', name: 'app_php_serialize')]
    public function phpSerialize(): Response
    {
        return $this->render('php/serialize.html.twig', [
            'controller_name' => 'PhpController',
            'title' => 'PHP Serialize',
        ]);
    }

    #[Route('/php/json', name: 'app_php_json')]
    public function phpUnserialize(): Response
    {
        return $this->render('php/json.html.twig', [
            'controller_name' => 'PhpController',
            'title' => 'PHP JSON',
        ]);
    }

    #[Route('/api/php/unserialize', name: 'api_php_unserialize', methods: ['POST'])]
    public function apiUnserialize(Request $request): JsonResponse
    {
        
        try {
            // Get data from the request body
            $content = $request->getContent();
            
            $mixed = unserialize($content);

            $str = '<?php' . PHP_EOL . var_export($mixed, true) . ';';

            return new JsonResponse(['success' => true, 'formatted' => $str], 200);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid PHP array input', 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/php/serialize', name: 'api_php_serialize', methods: ['POST'])]
    public function apiSerialize(Request $request): JsonResponse
    {
        
        try {
            // Get data from the request body
            $content = $request->getContent();
            
            $phpCode = base64_decode($content);
            
            $phpCode = str_replace('<?php', '', $phpCode);

            // Evaluate the PHP code string
            $mixed = eval('return ' . $phpCode);

            $str = serialize($mixed);

            return new JsonResponse(['success' => true, 'formatted' => $str], 200);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid PHP array input', 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/php/json', name: 'api_php_json', methods: ['POST'])]
    public function apiJson(Request $request): JsonResponse
    {
        // Get data from the request body
        $content = $request->getContent();

        $phpCode = base64_decode($content);

        try {
            // Evaluate the PHP code string
            $mixed = eval('return ' . $phpCode);

            if (is_array($mixed)) {
                // Convert array to JSON
                $json = json_encode($mixed, JSON_PRETTY_PRINT);
                return new JsonResponse(['success' => true, 'formatted' => $json], 200);
            } else if (is_object($mixed)) {
                // Convert array to JSON
                $json = json_encode($mixed, JSON_PRETTY_PRINT);
                return new JsonResponse(['success' => true, 'formatted' => $json], 200);
            }

            return new JsonResponse(['error' => 'Input did not evaluate to an array'], 400);
            

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid PHP array input', 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/php/json_decode', name: 'api_php_json_decode', methods: ['POST'])]
    public function apiJsonDecode(Request $request): JsonResponse
    {
        
        try {
            // Get data from the request body
            $content = $request->getContent();
            
            $mixed = json_decode($content);

            $str = '<?php' . PHP_EOL . var_export($mixed, true) . ';';

            return new JsonResponse(['success' => true, 'formatted' => $str], 200);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid PHP array input', 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/php/json_encode', name: 'api_php_json_encode', methods: ['POST'])]
    public function apiJsonEncode(Request $request): JsonResponse
    {
        
        try {
            // Get data from the request body
            $content = $request->getContent();
            
            $phpCode = base64_decode($content);
            
            $phpCode = str_replace('<?php', '', $phpCode);

            // Evaluate the PHP code string
            $mixed = eval('return ' . $phpCode);

            if (is_array($mixed)) {
                // Convert array to JSON
                $json = json_encode($mixed, JSON_PRETTY_PRINT);
                return new JsonResponse(['success' => true, 'formatted' => $json], 200);
            } else if (is_object($mixed)) {
                // Convert array to JSON
                $json = json_encode($mixed, JSON_PRETTY_PRINT);
                return new JsonResponse(['success' => true, 'formatted' => $json], 200);
            }

            return new JsonResponse(['error' => 'Input did not evaluate to an array'], 400);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid PHP array input', 'message' => $e->getMessage()], 400);
        }
    }
}
