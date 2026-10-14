<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Controller;

use App\Catalog\Application\Contracts\CategoryRepository;
use App\Catalog\Application\UseCase\ListProductsUseCase;
use App\FileStorage\Application\UseCase\ListStoreMediaLibraryUseCase;
use App\FileStorage\Application\SystemMediaCatalog;
use App\Storefront\Application\Dto\StorefrontStoreView;
use App\Storefront\Application\UseCase\ShowStorefrontHomeUseCase;
use App\Tenant\Application\Exception\InvalidTenantInput;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;
use App\Tenant\Application\UseCase\ShowStoreSettingsUseCase;
use App\Tenant\Application\UseCase\UpdateStoreAppearanceUseCase;
use App\Tenant\Presentation\Http\Form\UpdateStoreAppearanceForm;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Twig\Environment;

#[Route('/owner/store/appearance')]
final readonly class StoreAppearanceController
{
    public function __construct(
        private Environment $twig,
        private ShowStoreSettingsUseCase $showStoreSettings,
        private UpdateStoreAppearanceUseCase $updateAppearance,
        private UpdateStoreAppearanceForm $form,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private ListStoreMediaLibraryUseCase $listMediaLibrary,
        private CategoryRepository $categoryRepository,
        private ListProductsUseCase $listProducts,
        private ShowStorefrontHomeUseCase $showStorefrontHome,
        private SystemMediaCatalog $systemMediaCatalog,
    ) {
    }

    #[Route('', name: 'owner_store_appearance', methods: ['GET'])]
    public function show(Request $request): Response
    {
        $ownerEmail = $this->ownerEmail($request);
        if ($ownerEmail === null) {
            return new RedirectResponse('/auth/login');
        }

        try {
            $settings = $this->showStoreSettings->execute($ownerEmail);
        } catch (StoreSettingsAccessDenied $exception) {
            return new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return new Response($this->twig->render('tenant/store_appearance.html.twig', [
            'settings' => $settings,
            'appearanceTheme' => $this->appearanceTheme($settings->themeSettings),
            'hasAppearanceDraft' => isset($settings->themeSettings['appearance_draft']),
            'draftSaved' => $request->query->getBoolean('draft_saved'),
            'published' => $request->query->getBoolean('published'),
            'previewMissing' => $request->query->getBoolean('preview_missing'),
            'mediaImages' => $this->listMediaLibrary->execute($ownerEmail, 'image')->files,
            'categories' => $this->categoryRepository->listByStore($settings->storeId),
            'products' => $this->listProducts->execute($settings->storeId),
            'systemReviewAvatars' => $this->systemMediaCatalog->reviewAvatars(),
        ]));
    }

    #[Route('/preview', name: 'owner_store_appearance_preview', methods: ['GET'])]
    public function preview(Request $request): Response
    {
        $ownerEmail = $this->ownerEmail($request);
        if ($ownerEmail === null) {
            return new RedirectResponse('/auth/login');
        }

        try {
            $settings = $this->showStoreSettings->execute($ownerEmail);
        } catch (StoreSettingsAccessDenied $exception) {
            return new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        $draft = $settings->themeSettings['appearance_draft'] ?? null;
        if (!is_array($draft)) {
            return new RedirectResponse('/owner/store/appearance?preview_missing=1');
        }

        $store = new StorefrontStoreView(
            $settings->storeId,
            $settings->tenantId,
            $settings->name,
            $settings->slug,
            $settings->domain,
            $draft,
            $settings->defaultCurrency,
            $settings->contactEmail,
            $settings->contactPhone,
            $settings->contactCity,
            $settings->contactAddress,
        );
        $view = $this->showStorefrontHome->forStore($store);
        $response = new Response($this->twig->render('storefront/home.html.twig', [
            'view' => $view,
            'appearancePreview' => true,
        ]));
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }

    #[Route('', name: 'owner_store_appearance_update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        $ownerEmail = $this->ownerEmail($request);
        if ($ownerEmail === null) {
            return new RedirectResponse('/auth/login');
        }
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('store_appearance', $request->request->getString('_token')))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $input = $this->form->fromRequest($request, $ownerEmail);

        try {
            $this->updateAppearance->execute($input);
        } catch (InvalidTenantInput $exception) {
            $settings = $this->showStoreSettings->execute($ownerEmail);
            return new Response($this->twig->render('tenant/store_appearance.html.twig', [
                'settings' => $settings,
                'appearanceTheme' => $this->appearanceTheme($settings->themeSettings),
                'hasAppearanceDraft' => isset($settings->themeSettings['appearance_draft']),
                'error' => $exception->getMessage(),
                'mediaImages' => $this->listMediaLibrary->execute($ownerEmail, 'image')->files,
                'categories' => $this->categoryRepository->listByStore($settings->storeId),
                'products' => $this->listProducts->execute($settings->storeId),
                'systemReviewAvatars' => $this->systemMediaCatalog->reviewAvatars(),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (StoreSettingsAccessDenied $exception) {
            return new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        $result = $input->action === 'publish' ? 'published=1' : 'draft_saved=1';

        return new RedirectResponse('/owner/store/appearance?'.$result);
    }

    private function ownerEmail(Request $request): ?string
    {
        $email = $request->getSession()->get('shopsbox_auth_email');

        return is_string($email) && $email !== '' ? $email : null;
    }

    /**
     * @param array<string, mixed> $themeSettings
     *
     * @return array<string, mixed>
     */
    private function appearanceTheme(array $themeSettings): array
    {
        $draft = $themeSettings['appearance_draft'] ?? null;

        return is_array($draft) ? $draft : $themeSettings;
    }
}
