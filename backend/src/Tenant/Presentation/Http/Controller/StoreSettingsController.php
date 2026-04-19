<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Controller;

use App\Tenant\Application\Exception\InvalidTenantInput;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;
use App\Tenant\Application\UseCase\ShowStoreSettingsUseCase;
use App\Tenant\Application\UseCase\UpdateStoreSettingsUseCase;
use App\Tenant\Presentation\Http\Form\UpdateStoreSettingsForm;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/owner/store/settings')]
final readonly class StoreSettingsController
{
    public function __construct(
        private Environment $twig,
        private ShowStoreSettingsUseCase $showStoreSettings,
        private UpdateStoreSettingsUseCase $updateStoreSettings,
        private UpdateStoreSettingsForm $form,
    ) {
    }

    #[Route('', name: 'owner_store_settings', methods: ['GET'])]
    public function show(Request $request): Response
    {
        $ownerEmail = $this->ownerEmail($request);
        if ($ownerEmail === null) {
            return new RedirectResponse('/auth/code');
        }

        try {
            $view = $this->showStoreSettings->execute($ownerEmail);
        } catch (StoreSettingsAccessDenied $exception) {
            return new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return new Response($this->twig->render('tenant/store_settings.html.twig', [
            'settings' => $view,
        ]));
    }

    #[Route('', name: 'owner_store_settings_update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        $ownerEmail = $this->ownerEmail($request);
        if ($ownerEmail === null) {
            return new RedirectResponse('/auth/code');
        }

        try {
            $view = $this->updateStoreSettings->execute($this->form->fromRequest($request, $ownerEmail));
        } catch (InvalidTenantInput $exception) {
            $current = $this->showStoreSettings->execute($ownerEmail);

            return new Response($this->twig->render('tenant/store_settings.html.twig', [
                'settings' => $current,
                'error' => $exception->getMessage(),
            ]), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (StoreSettingsAccessDenied $exception) {
            return new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return new Response($this->twig->render('tenant/store_settings.html.twig', [
            'settings' => $view,
            'saved' => true,
        ]));
    }

    private function ownerEmail(Request $request): ?string
    {
        $email = $request->getSession()->get('shopsbox_auth_email');

        return is_string($email) && $email !== '' ? $email : null;
    }
}
