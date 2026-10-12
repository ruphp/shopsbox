<?php

declare(strict_types=1);

namespace App\Sales\Presentation\Http\Controller;

use App\Sales\Application\UseCase\ListOwnerOrdersUseCase;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;
use App\Tenant\Application\UseCase\ShowStoreSettingsUseCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

final readonly class OwnerOrderController
{
    public function __construct(
        private Environment $twig,
        private ShowStoreSettingsUseCase $showStoreSettings,
        private ListOwnerOrdersUseCase $listOrders,
    ) {
    }

    #[Route('/owner/orders', name: 'owner_orders', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $email = $request->getSession()->get('shopsbox_auth_email');
        if (!is_string($email) || $email === '') {
            return new RedirectResponse('/auth/login');
        }

        try {
            $settings = $this->showStoreSettings->execute($email);
        } catch (StoreSettingsAccessDenied $exception) {
            return new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return new Response($this->twig->render('tenant/orders.html.twig', [
            'settings' => $settings,
            'orders' => $this->listOrders->execute($email),
        ]));
    }
}
