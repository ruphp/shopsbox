<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Form;

use App\Catalog\Application\Dto\CreateProductInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class CreateProductForm
{
    public function fromRequest(Request $request, string $tenantId, string $storeId): CreateProductInput
    {
        $payload = $this->payload($request);

        $name = trim((string) ($payload['name'] ?? ''));
        $slug = trim((string) ($payload['slug'] ?? ''));
        if ($name === '' || $slug === '') {
            throw new BadRequestHttpException('Missing required fields.');
        }

        $categoryId = isset($payload['category_id']) ? trim((string) $payload['category_id']) : null;
        $description = isset($payload['description']) ? trim((string) $payload['description']) : null;
        $status = trim((string) ($payload['status'] ?? 'draft'));

        return new CreateProductInput(
            $tenantId,
            $storeId,
            $name,
            $slug,
            $categoryId === '' ? null : $categoryId,
            $description === '' ? null : $description,
            $status,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON payload.');
        }

        return $payload;
    }
}
