<?php

declare(strict_types=1);

namespace App\Storefront\Presentation\Http\Controller;

use App\Storefront\Application\Exception\StorefrontCategoryNotFound;
use App\Storefront\Application\Exception\StorefrontProductNotFound;
use App\Storefront\Application\Exception\StorefrontStoreNotFound;
use App\Storefront\Application\UseCase\ListStorefrontProductsUseCase;
use App\Storefront\Application\UseCase\ShowStorefrontHomeUseCase;
use App\Storefront\Application\UseCase\ShowStorefrontProductUseCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/s/{storeSlug}')]
final readonly class StorefrontController
{
    public function __construct(
        private Environment $twig,
        private ShowStorefrontHomeUseCase $showHome,
        private ListStorefrontProductsUseCase $listProducts,
        private ShowStorefrontProductUseCase $showProduct,
    ) {
    }

    #[Route('', name: 'storefront_home', methods: ['GET'])]
    public function home(string $storeSlug): Response
    {
        try {
            $view = $this->showHome->execute($storeSlug);
        } catch (StorefrontStoreNotFound $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        return new Response($this->twig->render('storefront/home.html.twig', [
            'view' => $view,
        ]));
    }

    #[Route('/products', name: 'storefront_products', methods: ['GET'])]
    public function products(string $storeSlug): Response
    {
        try {
            $view = $this->listProducts->execute($storeSlug);
        } catch (StorefrontStoreNotFound $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        return new Response($this->twig->render('storefront/products.html.twig', [
            'view' => $view,
        ]));
    }

    #[Route('/categories/{categorySlug}', name: 'storefront_category', methods: ['GET'])]
    public function category(string $storeSlug, string $categorySlug): Response
    {
        try {
            $view = $this->listProducts->byCategory($storeSlug, $categorySlug);
        } catch (StorefrontStoreNotFound|StorefrontCategoryNotFound $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        return new Response($this->twig->render('storefront/products.html.twig', [
            'view' => $view,
        ]));
    }

    #[Route('/products/{productSlug}', name: 'storefront_product', methods: ['GET'])]
    public function product(string $storeSlug, string $productSlug): Response
    {
        try {
            $view = $this->showProduct->execute($storeSlug, $productSlug);
        } catch (StorefrontStoreNotFound|StorefrontProductNotFound $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        }

        return new Response($this->twig->render('storefront/product.html.twig', [
            'view' => $view,
        ]));
    }
}
