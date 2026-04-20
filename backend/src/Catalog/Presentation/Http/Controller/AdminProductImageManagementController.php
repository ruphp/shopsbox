<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Controller;

use App\Catalog\Application\Contracts\EntityFlusher;
use App\Catalog\Application\Contracts\ProductImageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/stores/{storeId}/products/{productId}/images')]
final readonly class AdminProductImageManagementController
{
    public function __construct(
        private ProductImageRepository $productImageRepository,
        private EntityFlusher $entityFlusher,
    ) {
    }

    #[Route('', name: 'admin_catalog_product_images', methods: ['GET'])]
    public function list(string $storeId, string $productId): JsonResponse
    {
        return new JsonResponse([
            'images' => $this->productImageRepository->listByStoreAndProduct($storeId, $productId),
        ]);
    }

    #[Route('/{imageId}/primary', name: 'admin_catalog_product_image_primary', methods: ['POST'])]
    public function primary(string $storeId, string $productId, string $imageId): JsonResponse
    {
        $image = $this->productImageRepository->setPrimary($storeId, $productId, $imageId);
        if ($image === null) {
            return new JsonResponse(['error' => 'not_found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityFlusher->flush();

        return new JsonResponse($image);
    }

    #[Route('/{imageId}/position', name: 'admin_catalog_product_image_position', methods: ['POST'])]
    public function position(Request $request, string $storeId, string $productId, string $imageId): JsonResponse
    {
        $image = $this->productImageRepository->changePosition($storeId, $productId, $imageId, $request->request->getInt('position', 0));
        if ($image === null) {
            return new JsonResponse(['error' => 'not_found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityFlusher->flush();

        return new JsonResponse($image);
    }

    #[Route('/{imageId}', name: 'admin_catalog_product_image_delete', methods: ['DELETE'])]
    public function delete(string $storeId, string $productId, string $imageId): JsonResponse
    {
        if (!$this->productImageRepository->delete($storeId, $productId, $imageId)) {
            return new JsonResponse(['error' => 'not_found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityFlusher->flush();

        return new JsonResponse(['deleted' => true]);
    }
}
