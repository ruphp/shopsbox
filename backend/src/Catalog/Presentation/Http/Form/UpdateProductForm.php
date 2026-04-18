<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Form;

use App\Catalog\Application\Dto\UpdateProductInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class UpdateProductForm
{
    public function fromRequest(Request $request, string $storeId, string $productId): UpdateProductInput
    {
        $payload = $this->payload($request);

        $name = trim((string) ($payload['name'] ?? ''));
        $slug = trim((string) ($payload['slug'] ?? ''));
        if ($name === '' || $slug === '') {
            throw new BadRequestHttpException('Missing required fields.');
        }

        $categoryId = isset($payload['category_id']) ? trim((string) $payload['category_id']) : null;
        $description = isset($payload['description']) ? trim((string) $payload['description']) : null;

        return new UpdateProductInput(
            $storeId,
            $productId,
            $name,
            $slug,
            $categoryId === '' ? null : $categoryId,
            $description === '' ? null : $description,
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
