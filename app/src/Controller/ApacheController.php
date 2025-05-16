<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApacheController extends AbstractController
{
    #[Route('/apache/vhost', name: 'app_apache_vhost')]
    public function appVhost(): Response
    {
        return $this->render('apache/vhost.html.twig', [
            'controller_name' => 'ApacheController',
            'title' => 'VirtualHost',
        ]);
    }

    #[Route('/apache/redirections', name: 'app_apache_redirections')]
    public function appRedirections(): Response
    {
        return $this->render('apache/redirections.html.twig', [
            'controller_name' => 'ApacheController',
            'title' => 'Redirections',
        ]);
    }

    #[Route('/api/apache/vhost', name: 'api_apache_vhost', methods: ['POST'])]
    public function apiApacheVhost(Request $request): JsonResponse
    {
        try {
            // Get data from the request body
            $body = $request->getContent();
            if (empty($body)) {
                return new JsonResponse(['error' => 'No content provided'], 400);
            }
            $body = json_decode($body, true);

            // dots to underscores
            $underscoreName = str_replace('.', '_', $body['serverName'] ?? '');

            $vars = [
                'logName' => $underscoreName,
                'serverName' => $body['serverName'] ?? '',
                'serverAlias' => $body['serverAlias'] ?? '',
                'documentRoot' => $body['documentRoot'] ?? '',
                'directory' => $body['directory'] ?? '',
                'httpPort' => $body['httpPort'] ?? '80',
                'httpsPort' => $body['httpsPort'] ?? '443',
                'bindIP' => $body['bindIP'] ?? '*',
                'preferences' => [
                    'http' => in_array('http', $body['preferences'] ?? []),
                    'https' => in_array('https', $body['preferences'] ?? []),
                    'serverAlias' => in_array('serverAlias', $body['preferences'] ?? []),
                ],
            ];

            $str = $this->renderView('apache/vhost.conf.twig', $vars);

            return new JsonResponse(['success' => true, 'formatted' => $str, 'filename' => $underscoreName . '.conf'], 200);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid input', 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/apache/redirections', name: 'api_apache_redirections', methods: ['POST'])]
    public function apiApacheRedirections(Request $request): JsonResponse
    {
        try {
            // Get data from the request body
            $content = $request->getContent();

            if (empty($content)) {
                return new JsonResponse(['error' => 'No content provided'], 400);
            }

            $lines = explode("\n", $content);
            $arr = [];

            foreach ($lines as $line) {
                $ex = explode(" ", $line);

                if( count($ex) === 4 ) {
                    // Redirect 301 /_SHOP/files/products/5010010001561.jpg?id=8169535 /
                    $from = parse_url($ex[2]);
                    $to = parse_url($ex[3]);
                    $status = $ex[1];

                    // check if from path has trailing slash
                    if (substr($from['path'], -1) === '/') {
                        $from['path'] = substr($from['path'], 0, -1);
                        $from['path'] .= '/?';
                    }

                    // remove leading slash from path
                    if (substr($from['path'], -1) === '/') {
                        $from['path'] = substr($from['path'], 0, -1);
                    }

                    if (!empty($from['query'])) {
                        $arr[] = "RewriteCond %{QUERY_STRING} ^" . $from['query'] . "$";
                        $arr[] = "RewriteRule ^" . $from['path'] . "$ " . rtrim($to['path'], '_') . "? [R=".$status.",L]";
                        $arr[] = "";
                    } else {
                        $arr[] = "RewriteRule ^" . $from['path'] . "$ " . rtrim($to['path'], '_') . "? [R=".$status.",L]";
                        $arr[] = "";
                    }

                } else if( count($ex) === 3  ) {
                    // /_SHOP/files/products/5010010001561.jpg?id=8169535 / 301
                    $from = parse_url($ex[0]);
                    $to = parse_url($ex[1]);
                    $status = $ex[2];

                    // check if from path has trailing slash
                    if (substr($from['path'], -1) === '/') {
                        $from['path'] = substr($from['path'], 0, -1);
                        $from['path'] .= '/?';
                    }

                    // remove leading slash from path
                    if (substr($from['path'], -1) === '/') {
                        $from['path'] = substr($from['path'], 0, -1);
                    }

                    if (!empty($from['query'])) {
                        $arr[] = "RewriteCond %{QUERY_STRING} ^" . $from['query'] . "$";
                        $arr[] = "RewriteRule ^" . $from['path'] . "$ " . rtrim($to['path'], '_') . "? [R=".$status.",L]";
                        $arr[] = "";
                    } else {
                        $arr[] = "RewriteRule ^" . $from['path'] . "$ " . rtrim($to['path'], '_') . "? [R=".$status.",L]";
                        $arr[] = "";
                    }

                } else if ( count($ex) === 2) {
                    // /_SHOP/files/products/5010010001561.jpg?id=8169535 /
                    $from = parse_url($ex[0]);
                    $to = parse_url($ex[1]);
                    $status = '301';

                    // check if from path has trailing slash
                    if (substr($from['path'], -1) === '/') {
                        $from['path'] = substr($from['path'], 0, -1);
                        $from['path'] .= '/?';
                    }

                    // remove leading slash from path
                    if (substr($from['path'], -1) === '/') {
                        $from['path'] = substr($from['path'], 0, -1);
                    }

                    if (!empty($from['query'])) {
                        $arr[] = "RewriteCond %{QUERY_STRING} ^" . $from['query'] . "$";
                        $arr[] = "RewriteRule ^" . $from['path'] . "$ " . rtrim($to['path'], '_') . "? [R=".$status.",L]";
                        $arr[] = "";
                    } else {
                        $arr[] = "RewriteRule ^" . $from['path'] . "$ " . rtrim($to['path'], '_') . "? [R=".$status.",L]";
                        $arr[] = "";
                    }
                } else {
                    $arr[] = "Unknown format for line: " . $line;
                }
            }

            $str = implode("\n", $arr);

            return new JsonResponse(['success' => true, 'formatted' => $str], 200);

        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid input', 'message' => $e->getMessage()], 400);
        }
    }
}