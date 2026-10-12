<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\Controller;

use App\Catalog\Application\Contracts\CategoryRepository;
use App\Catalog\Application\Contracts\EntityFlusher;
use App\Catalog\Application\Contracts\ProductImageRepository;
use App\Catalog\Application\Dto\ChangeProductStatusInput;
use App\Catalog\Application\Dto\CreateProductInput;
use App\Catalog\Application\Dto\ProductView;
use App\Catalog\Application\Dto\UpdateProductInput;
use App\Catalog\Application\Dto\UploadProductImageInput;
use App\Catalog\Application\Exception\InvalidProductImageInput;
use App\Catalog\Application\Exception\InvalidProductInput;
use App\Catalog\Application\Exception\InvalidProductStatusTransition;
use App\Catalog\Application\Exception\ProductNotFound;
use App\Catalog\Application\UseCase\ChangeProductStatusUseCase;
use App\Catalog\Application\UseCase\CreateProductUseCase;
use App\Catalog\Application\UseCase\ListProductsUseCase;
use App\Catalog\Application\UseCase\UpdateProductUseCase;
use App\Catalog\Application\UseCase\UploadProductImageUseCase;
use App\Tenant\Application\Dto\StoreSettingsView;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;
use App\Tenant\Application\UseCase\ShowStoreSettingsUseCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

#[Route('/owner/products')]
final readonly class OwnerProductController
{
    public function __construct(
        private Environment $twig,
        private ShowStoreSettingsUseCase $showStoreSettings,
        private ListProductsUseCase $listProducts,
        private CreateProductUseCase $createProduct,
        private UpdateProductUseCase $updateProduct,
        private ChangeProductStatusUseCase $changeProductStatus,
        private UploadProductImageUseCase $uploadProductImage,
        private CategoryRepository $categoryRepository,
        private ProductImageRepository $productImageRepository,
        private EntityFlusher $entityFlusher,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('', name: 'owner_products', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $settings = $this->settings($request);
        if (!$settings instanceof StoreSettingsView) {
            return $settings;
        }

        return new Response($this->twig->render('tenant/products.html.twig', [
            'settings' => $settings,
            'products' => $this->listProducts->execute($settings->storeId),
            'notice' => $request->query->getString('notice'),
        ]));
    }

    #[Route('/new', name: 'owner_product_new', methods: ['GET'])]
    public function new(Request $request): Response
    {
        $settings = $this->settings($request);
        if (!$settings instanceof StoreSettingsView) {
            return $settings;
        }

        return $this->renderForm($settings);
    }

    #[Route('/new', name: 'owner_product_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $settings = $this->settings($request);
        if (!$settings instanceof StoreSettingsView) {
            return $settings;
        }
        if (!$this->validCsrf($request, 'owner_product_create')) {
            return $this->renderForm($settings, null, 'Форма устарела. Обновите страницу и повторите сохранение.', $request);
        }

        try {
            $product = $this->createProduct->execute(new CreateProductInput(
                $settings->tenantId,
                $settings->storeId,
                $request->request->getString('name'),
                $request->request->getString('slug'),
                $this->nullable($request->request->getString('category_id')),
                $this->nullable($request->request->getString('description')),
                'draft',
                $request->request->getString('price'),
            ));
        } catch (InvalidProductInput $exception) {
            return $this->renderForm($settings, null, $this->productError($exception), $request);
        }

        return new RedirectResponse('/owner/products/'.$product->id.'?notice=created');
    }

    #[Route('/{productId}', name: 'owner_product_edit', methods: ['GET'])]
    public function edit(Request $request, string $productId): Response
    {
        $settings = $this->settings($request);
        if (!$settings instanceof StoreSettingsView) {
            return $settings;
        }

        $product = $this->ownedProduct($settings->storeId, $productId);
        if (!$product instanceof ProductView) {
            return new Response('Товар не найден.', Response::HTTP_NOT_FOUND);
        }

        return $this->renderForm($settings, $product, null, null, $request->query->getString('notice'));
    }

    #[Route('/{productId}', name: 'owner_product_update', methods: ['POST'])]
    public function update(Request $request, string $productId): Response
    {
        $settings = $this->settings($request);
        if (!$settings instanceof StoreSettingsView) {
            return $settings;
        }

        $product = $this->ownedProduct($settings->storeId, $productId);
        if (!$product instanceof ProductView) {
            return new Response('Товар не найден.', Response::HTTP_NOT_FOUND);
        }
        if (!$this->validCsrf($request, 'owner_product_update_'.$productId)) {
            return $this->renderForm($settings, $product, 'Форма устарела. Обновите страницу и повторите сохранение.', $request);
        }

        try {
            $this->updateProduct->execute(new UpdateProductInput(
                $settings->storeId,
                $productId,
                $request->request->getString('name'),
                $request->request->getString('slug'),
                $this->nullable($request->request->getString('category_id')),
                $this->nullable($request->request->getString('description')),
                $request->request->getString('price'),
            ));
        } catch (InvalidProductInput $exception) {
            return $this->renderForm($settings, $product, $this->productError($exception), $request);
        } catch (ProductNotFound) {
            return new Response('Товар не найден.', Response::HTTP_NOT_FOUND);
        }

        return new RedirectResponse('/owner/products/'.$productId.'?notice=saved');
    }

    #[Route('/{productId}/status/{status}', name: 'owner_product_status', methods: ['POST'])]
    public function status(Request $request, string $productId, string $status): Response
    {
        $settings = $this->settings($request);
        if (!$settings instanceof StoreSettingsView) {
            return $settings;
        }
        if (!$this->ownedProduct($settings->storeId, $productId) instanceof ProductView) {
            return new Response('Товар не найден.', Response::HTTP_NOT_FOUND);
        }
        if (!$this->validCsrf($request, 'owner_product_status_'.$productId)) {
            return new Response('Форма устарела. Обновите страницу.', Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->changeProductStatus->execute(new ChangeProductStatusInput($settings->storeId, $productId, $status));
        } catch (InvalidProductInput|InvalidProductStatusTransition $exception) {
            return $this->renderForm(
                $settings,
                $this->ownedProduct($settings->storeId, $productId),
                $exception instanceof InvalidProductStatusTransition
                    ? 'Из архива товар восстановить нельзя.'
                    : 'Неизвестный статус товара.',
            );
        } catch (ProductNotFound) {
            return new Response('Товар не найден.', Response::HTTP_NOT_FOUND);
        }

        return new RedirectResponse('/owner/products/'.$productId.'?notice=status');
    }

    #[Route('/{productId}/images', name: 'owner_product_image_upload', methods: ['POST'])]
    public function uploadImage(Request $request, string $productId): Response
    {
        $settings = $this->settings($request);
        if (!$settings instanceof StoreSettingsView) {
            return $settings;
        }
        $product = $this->ownedProduct($settings->storeId, $productId);
        if (!$product instanceof ProductView) {
            return new Response('Товар не найден.', Response::HTTP_NOT_FOUND);
        }
        if (!$this->validCsrf($request, 'owner_product_images_'.$productId)) {
            return $this->renderForm($settings, $product, 'Форма устарела. Обновите страницу и повторите загрузку.');
        }

        $file = $request->files->get('image');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return $this->renderForm($settings, $product, 'Выберите изображение для загрузки.');
        }

        try {
            $contents = file_get_contents($file->getPathname());
            $this->uploadProductImage->execute(new UploadProductImageInput(
                $settings->storeId,
                $productId,
                $file->getClientOriginalName(),
                (string) $file->getMimeType(),
                $file->getSize(),
                is_string($contents) ? $contents : '',
            ));
        } catch (InvalidProductImageInput $exception) {
            return $this->renderForm($settings, $product, $this->imageError($exception));
        }

        return new RedirectResponse('/owner/products/'.$productId.'?notice=image');
    }

    #[Route('/{productId}/images/{imageId}/primary', name: 'owner_product_image_primary', methods: ['POST'])]
    public function primaryImage(Request $request, string $productId, string $imageId): Response
    {
        return $this->changeImage($request, $productId, $imageId, false);
    }

    #[Route('/{productId}/images/{imageId}/delete', name: 'owner_product_image_remove', methods: ['POST'])]
    public function removeImage(Request $request, string $productId, string $imageId): Response
    {
        return $this->changeImage($request, $productId, $imageId, true);
    }

    private function changeImage(Request $request, string $productId, string $imageId, bool $delete): Response
    {
        $settings = $this->settings($request);
        if (!$settings instanceof StoreSettingsView) {
            return $settings;
        }
        if (!$this->ownedProduct($settings->storeId, $productId) instanceof ProductView) {
            return new Response('Товар не найден.', Response::HTTP_NOT_FOUND);
        }
        if (!$this->validCsrf($request, 'owner_product_images_'.$productId)) {
            return new Response('Форма устарела. Обновите страницу.', Response::HTTP_BAD_REQUEST);
        }

        $changed = $delete
            ? $this->productImageRepository->delete($settings->storeId, $productId, $imageId)
            : $this->productImageRepository->setPrimary($settings->storeId, $productId, $imageId) !== null;
        if (!$changed) {
            return new Response('Изображение не найдено.', Response::HTTP_NOT_FOUND);
        }
        $this->entityFlusher->flush();

        return new RedirectResponse('/owner/products/'.$productId.'?notice='.($delete ? 'image_deleted' : 'primary'));
    }

    private function renderForm(
        StoreSettingsView $settings,
        ?ProductView $product = null,
        ?string $error = null,
        ?Request $request = null,
        string $notice = '',
    ): Response {
        return new Response($this->twig->render('tenant/product_form.html.twig', [
            'settings' => $settings,
            'product' => $product,
            'categories' => $this->categoryRepository->listByStore($settings->storeId),
            'images' => $product instanceof ProductView
                ? $this->productImageRepository->listByStoreAndProduct($settings->storeId, $product->id)
                : [],
            'form' => $request instanceof Request ? $request->request->all() : [],
            'error' => $error,
            'notice' => $notice,
        ]), $error === null ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function settings(Request $request): StoreSettingsView|Response
    {
        $email = $request->getSession()->get('shopsbox_auth_email');
        if (!is_string($email) || $email === '') {
            return new RedirectResponse('/auth/login');
        }

        try {
            return $this->showStoreSettings->execute($email);
        } catch (StoreSettingsAccessDenied $exception) {
            return new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }

    private function ownedProduct(string $storeId, string $productId): ?ProductView
    {
        foreach ($this->listProducts->execute($storeId) as $product) {
            if ($product->id === $productId) {
                return $product;
            }
        }

        return null;
    }

    private function validCsrf(Request $request, string $id): bool
    {
        return $this->csrfTokenManager->isTokenValid(new CsrfToken($id, $request->request->getString('_token')));
    }

    private function nullable(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function productError(InvalidProductInput $exception): string
    {
        return match ($exception->field) {
            'name' => 'Укажите название товара длиной до 180 символов.',
            'slug' => str_contains($exception->getMessage(), 'already used')
                ? 'Такой адрес товара уже занят.'
                : 'Адрес товара может содержать только строчные латинские буквы, цифры и дефисы.',
            'category_id' => 'Выбранная категория не принадлежит этому магазину.',
            'price' => 'Укажите цену числом, не более двух знаков после запятой.',
            default => 'Проверьте заполненные поля.',
        };
    }

    private function imageError(InvalidProductImageInput $exception): string
    {
        return match ($exception->field) {
            'mime_type' => 'Поддерживаются изображения JPG, PNG, WebP и GIF.',
            'image' => str_contains($exception->getMessage(), 'too large')
                ? 'Размер изображения не должен превышать 5 МБ.'
                : 'Не удалось загрузить изображение или исчерпан лимит медиатеки.',
            default => 'Не удалось загрузить изображение.',
        };
    }
}
