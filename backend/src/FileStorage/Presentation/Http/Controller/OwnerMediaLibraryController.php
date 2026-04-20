<?php

declare(strict_types=1);

namespace App\FileStorage\Presentation\Http\Controller;

use App\FileStorage\Application\UseCase\ListStoreMediaLibraryUseCase;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;
use App\Tenant\Application\UseCase\ShowStoreSettingsUseCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/owner/media')]
final readonly class OwnerMediaLibraryController
{
    public function __construct(
        private Environment $twig,
        private ShowStoreSettingsUseCase $showStoreSettings,
        private ListStoreMediaLibraryUseCase $listMediaLibrary,
    ) {
    }

    #[Route('', name: 'owner_media_library', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $ownerEmail = $this->ownerEmail($request);
        if ($ownerEmail === null) {
            return new RedirectResponse('/auth/code');
        }

        try {
            $settings = $this->showStoreSettings->execute($ownerEmail);
            $library = $this->listMediaLibrary->execute($ownerEmail, $request->query->getString('type'));
        } catch (StoreSettingsAccessDenied $exception) {
            return new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        return new Response($this->twig->render('tenant/media_library.html.twig', [
            'settings' => $settings,
            'library' => $library,
            'currentType' => $request->query->getString('type'),
        ]));
    }

    private function ownerEmail(Request $request): ?string
    {
        $email = $request->getSession()->get('shopsbox_auth_email');

        return is_string($email) && $email !== '' ? $email : null;
    }
}
