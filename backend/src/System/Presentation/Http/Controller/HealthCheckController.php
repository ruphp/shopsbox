<?php

declare(strict_types=1);

namespace App\System\Presentation\Http\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthCheckController
{
    #[Route('/health', name: 'health_check', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'service' => 'shopsbox_backend',
            'status' => 'ok',
        ]);
    }
}
