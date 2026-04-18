<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Controller;

use App\Catalog\Application\Dto\ChangeProductStatusInput;
use App\Catalog\Application\Dto\ProductView;
use App\Catalog\Application\Exception\InvalidProductInput;
use App\Catalog\Application\Exception\InvalidProductStatusTransition;
use App\Catalog\Application\Exception\ProductNotFound;
use App\Catalog\Application\UseCase\ChangeProductStatusUseCase;
use App\Catalog\Application\UseCase\CreateProductUseCase;
use App\Catalog\Application\UseCase\ListProductsUseCase;
use App\Catalog\Application\UseCase\UpdateProductUseCase;
use App\Catalog\Domain\ProductStatus;
use App\Catalog\Presentation\Http\Form\CreateProductForm;
use App\Catalog\Presentation\Http\Form\UpdateProductForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tenants/{tenantId}/stores/{storeId}/products')]
final readonly class AdminProductController
{
    public function __construct(
        private ListProductsUseCase $listProducts,
        private CreateProductUseCase $createProduct,
        private UpdateProductUseCase $updateProduct,
        private ChangeProductStatusUseCase $changeProductStatus,
        private CreateProductForm $createProductForm,
        private UpdateProductForm $updateProductForm,
    ) {
    }

    #[Route('', name: 'admin_catalog_products_list', methods: ['GET'])]
    public function list(string $storeId): JsonResponse
    {
        return new JsonResponse([
            'items' => array_map(
                fn (ProductView $product): array => $this->productToArray($product),
                $this->listProducts->execute($storeId),
            ),
        ]);
    }

    #[Route('', name: 'admin_catalog_products_create', methods: ['POST'])]
    public function create(Request $request, string $tenantId, string $storeId): JsonResponse
    {
        try {
            $input = $this->createProductForm->fromRequest($request, $tenantId, $storeId);
            $product = $this->createProduct->execute($input);
        } catch (BadRequestHttpException $exception) {
            return $this->badRequest('bad_request', null, $exception->getMessage());
        } catch (InvalidProductInput $exception) {
            return $this->badRequest('validation_error', $exception->field, $exception->getMessage());
        }

        return new JsonResponse($this->productToArray($product), JsonResponse::HTTP_CREATED);
    }

    #[Route('/{productId}', name: 'admin_catalog_products_update', methods: ['PATCH'])]
    public function update(Request $request, string $storeId, string $productId): JsonResponse
    {
        try {
            $input = $this->updateProductForm->fromRequest($request, $storeId, $productId);
            $product = $this->updateProduct->execute($input);
        } catch (BadRequestHttpException $exception) {
            return $this->badRequest('bad_request', null, $exception->getMessage());
        } catch (InvalidProductInput $exception) {
            return $this->badRequest('validation_error', $exception->field, $exception->getMessage());
        } catch (ProductNotFound $exception) {
            return $this->notFound($exception->getMessage());
        }

        return new JsonResponse($this->productToArray($product));
    }

    #[Route('/{productId}/publish', name: 'admin_catalog_products_publish', methods: ['POST'])]
    public function publish(string $storeId, string $productId): JsonResponse
    {
        return $this->changeStatus($storeId, $productId, ProductStatus::ACTIVE);
    }

    #[Route('/{productId}/hide', name: 'admin_catalog_products_hide', methods: ['POST'])]
    public function hide(string $storeId, string $productId): JsonResponse
    {
        return $this->changeStatus($storeId, $productId, ProductStatus::DRAFT);
    }

    #[Route('/{productId}/archive', name: 'admin_catalog_products_archive', methods: ['POST'])]
    public function archive(string $storeId, string $productId): JsonResponse
    {
        return $this->changeStatus($storeId, $productId, ProductStatus::ARCHIVED);
    }

    private function changeStatus(string $storeId, string $productId, ProductStatus $status): JsonResponse
    {
        try {
            $product = $this->changeProductStatus->execute(new ChangeProductStatusInput(
                $storeId,
                $productId,
                $status->value,
            ));
        } catch (InvalidProductInput $exception) {
            return $this->badRequest('validation_error', $exception->field, $exception->getMessage());
        } catch (InvalidProductStatusTransition $exception) {
            return $this->badRequest('invalid_status_transition', 'status', $exception->getMessage());
        } catch (ProductNotFound $exception) {
            return $this->notFound($exception->getMessage());
        }

        return new JsonResponse($this->productToArray($product));
    }

    /**
     * @return array<string, mixed>
     */
    private function productToArray(ProductView $product): array
    {
        return [
            'id' => $product->id,
            'tenant_id' => $product->tenantId,
            'store_id' => $product->storeId,
            'category_id' => $product->categoryId,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'status' => $product->status,
            'created_at' => $product->createdAt,
            'updated_at' => $product->updatedAt,
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

    private function notFound(string $message): JsonResponse
    {
        return new JsonResponse([
            'error' => 'not_found',
            'message' => $message,
        ], JsonResponse::HTTP_NOT_FOUND);
    }
}
