<?php

declare(strict_types=1);

namespace App\FileStorage\Presentation\Http\Controller;

use App\FileStorage\Application\Contracts\FileStorage;
use League\Flysystem\UnableToReadFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ServeFileController
{
    public function __construct(private FileStorage $fileStorage)
    {
    }

    #[Route('/files/{key}', name: 'file_storage_serve', requirements: ['key' => '.+'], methods: ['GET'])]
    public function __invoke(string $key): Response
    {
        try {
            $contents = $this->fileStorage->read($key);
        } catch (UnableToReadFile) {
            return new Response('File not found.', Response::HTTP_NOT_FOUND);
        }

        return new Response($contents, Response::HTTP_OK, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }
}
