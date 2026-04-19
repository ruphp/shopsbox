<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Controller;

use App\Catalog\Application\Dto\ProductImageView;
use App\Catalog\Application\Exception\InvalidProductImageInput;
use App\Catalog\Application\Exception\ProductNotFound;
use App\Catalog\Application\UseCase\UploadProductImageUseCase;
use App\Catalog\Presentation\Http\Form\UploadProductImageForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tenants/{tenantId}/stores/{storeId}/products/{productId}/image')]
final readonly class AdminProductImageController
{
    public function __construct(
        private UploadProductImageUseCase $uploadProductImage,
        private UploadProductImageForm $form,
    ) {
    }

    #[Route('', name: 'admin_catalog_product_image_upload', methods: ['POST'])]
    public function upload(Request $request, string $storeId, string $productId): JsonResponse
    {
        try {
            $input = $this->form->fromRequest($request, $storeId, $productId);
            $image = $this->uploadProductImage->execute($input);
        } catch (BadRequestHttpException $exception) {
            return $this->badRequest('bad_request', null, $exception->getMessage());
        } catch (InvalidProductImageInput $exception) {
            return $this->badRequest('validation_error', $exception->field, $exception->getMessage());
        } catch (ProductNotFound $exception) {
            return new JsonResponse([
                'error' => 'not_found',
                'message' => $exception->getMessage(),
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->imageToArray($image), JsonResponse::HTTP_CREATED);
    }

    /**
     * @return array<string, mixed>
     */
    private function imageToArray(ProductImageView $image): array
    {
        return [
            'id' => $image->id,
            'product_id' => $image->productId,
            'key' => $image->key,
            'public_url' => $image->publicUrl,
            'mime_type' => $image->mimeType,
            'size' => $image->size,
            'created_at' => $image->createdAt,
        ];
    }

    private function badRequest(string $error, ?string $field, string $message): JsonResponse
    {
        return new JsonResponse(array_filter([
            'error' => $error,
            'field' => $field,
            'message' => $message,
        ], static fn (mixed $value): bool => $value !== null), JsonResponse::HTTP_BAD_REQUEST);
    }
}
