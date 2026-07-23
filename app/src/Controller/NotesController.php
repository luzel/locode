<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotesController extends AbstractController
{
    #[Route('/notes', name: 'app_notes')]
    public function index(): Response
    {
        return $this->render('notes/index.html.twig', [
            'controller_name' => 'NotesController',
            'title' => 'Notes',
        ]);
    }

    #[Route('/api/notes/files', name: 'api_notes_files', methods: ['GET'])]
    public function getFileList(Request $request): JsonResponse
    {
        try {
            $rootPath = $this->getParameter('kernel.project_dir');
            $files = scandir($rootPath . '/notes');
            $files = array_diff($files, ['.', '..']);
            $ignoreFiles = ['.gitignore', '.gitkeep', '.DS_Store'];

            $arr = [];
            foreach ($files as $file) {

                // ignore files
                if (in_array($file, $ignoreFiles)) {
                    continue;
                }

                $size = filesize($rootPath . '/notes/' . $file);

                $arr[] = [
                    'name' => $file,
                    'size' => $size,
                    'size_formated' => $this->formatSize($size),
                    'updated_at' => filemtime($rootPath . '/notes/' . $file),
                    'created_at' => filectime($rootPath . '/notes/' . $file), 
                ];
            }

            
            // possible valuse: are name_asc, name_desc, size_asc, size_desc
            $sort = $request->query->get('sort', 'updated_at,desc');
            $sort = explode(',', $sort);
            $sortField = $sort[0];
            $sortOrder = $sort[1];

            if( !in_array($sortField, ['name', 'size', 'updated_at']) || !in_array($sortOrder, ['asc', 'desc']) ) {
                throw new \Exception('Invalid sort field or order');
            }

            // sort arr by query param sort
            usort($arr, function($a, $b) use ($sortField, $sortOrder) {
                if ($sortOrder === 'asc') {
                    return strtolower( $a[$sortField] ) <=> strtolower( $b[$sortField] );
                } else {
                    return strtolower( $b[$sortField] ) <=> strtolower( $a[$sortField] );
                }
            });

            return new JsonResponse(['success' => true, 'files' => $arr], 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid input', 'message' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/notes/{base64}', name: 'api_notes_file', methods: ['GET'])]
    public function getNoteContent(string $base64): Response
    {
        $rootPath = $this->getParameter('kernel.project_dir');
        $filename = base64_decode($base64);
        $filePath = $rootPath . '/notes/' . $filename;

        if (!file_exists($filePath)) {
            return new JsonResponse(['error' => 'File '. $filename .' not found'], Response::HTTP_NOT_FOUND);
        }

        $content = file_get_contents($filePath);

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'text/plain'
        ]);
    }

    #[Route('/api/notes/{base64}', name: 'api_notes_content', methods: ['POST'])]
    public function saveNoteContent(string $base64, Request $request): Response
    {
        $rootPath = $this->getParameter('kernel.project_dir');
        $fileName = base64_decode($base64);
        $filePath = $rootPath . '/notes/' . $fileName;

        $data = json_decode($request->getContent(), true);
        if (!isset($data['content'])) {
            return new JsonResponse(['error' => 'Invalid content'], Response::HTTP_BAD_REQUEST);
        }

        file_put_contents($filePath, $data['content']);

        // read X-File-Name header. Check if name diffs and rename file
        $newFileName = $request->headers->get('X-File-Name');
        if ($newFileName) {

            if ($newFileName !== $fileName) {
                $newFilePath = $rootPath . '/notes/' . $newFileName;
                rename($filePath, $newFilePath);
            }
        }

        return new JsonResponse(['message' => 'File saved successfully'], Response::HTTP_OK);
    }

    #[Route('/api/notes', name: 'api_notes_new_content', methods: ['POST'])]
    public function newNoteContent(): Response
    {
        $rootPath = $this->getParameter('kernel.project_dir');
        $fileName = 'new.md';
        $filePath = $rootPath . '/notes/' . $fileName;

        // do while loop to create new file name if file exists
        $i = 1;
        while (file_exists($filePath)) {
            $fileName = 'new' . $i . '.md';
            $filePath = $rootPath . '/notes/' . $fileName;
            $i++;
        }

        file_put_contents($filePath, '');

        return new Response('', Response::HTTP_OK, [
            'Content-Type' => 'text/plain',
            'X-File-Name' => $fileName
        ]);
    }

    private function formatSize(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);
        
        return sprintf("%.{$precision}f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }
}
