<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Controller;

use App\FileStorage\Application\UseCase\ListStoreMediaLibraryUseCase;
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
            'saved' => $request->query->getBoolean('saved'),
            'mediaImages' => $this->listMediaLibrary->execute($ownerEmail, 'image')->files,
        ]));
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

        try {
            $this->updateAppearance->execute($this->form->fromRequest($request, $ownerEmail));
        } catch (InvalidTenantInput $exception) {
            return new Response($this->twig->render('tenant/store_appearance.html.twig', [
                'settings' => $this->showStoreSettings->execute($ownerEmail),
                'error' => $exception->getMessage(),
                'mediaImages' => $this->listMediaLibrary->execute($ownerEmail, 'image')->files,
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (StoreSettingsAccessDenied $exception) {
            return new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return new RedirectResponse('/owner/store/appearance?saved=1');
    }

    private function ownerEmail(Request $request): ?string
    {
        $email = $request->getSession()->get('shopsbox_auth_email');

        return is_string($email) && $email !== '' ? $email : null;
    }
}
