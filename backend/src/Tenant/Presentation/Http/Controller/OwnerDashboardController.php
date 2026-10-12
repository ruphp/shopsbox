<?php

declare(strict_types=1);

namespace App\Tenant\Presentation\Http\Controller;

use App\Catalog\Application\UseCase\ListProductsUseCase;
use App\Tenant\Application\Contracts\UserPasswordRepository;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;
use App\Tenant\Application\UseCase\ShowStoreSettingsUseCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/owner')]
final readonly class OwnerDashboardController
{
    public function __construct(
        private Environment $twig,
        private ShowStoreSettingsUseCase $showStoreSettings,
        private ListProductsUseCase $listProducts,
        private UserPasswordRepository $users,
    ) {
    }

    #[Route('', name: 'owner_dashboard', methods: ['GET'])]
    public function index(Request $request): Response
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

        return new Response($this->twig->render('tenant/owner_dashboard.html.twig', [
            'settings' => $settings,
            'phoneVerified' => $this->users->hasVerifiedPhone($ownerEmail),
            'productCount' => count($this->listProducts->execute($settings->storeId)),
        ]));
    }

    private function ownerEmail(Request $request): ?string
    {
        $email = $request->getSession()->get('shopsbox_auth_email');

        return is_string($email) && $email !== '' ? $email : null;
    }

}
