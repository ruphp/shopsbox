<?php

declare(strict_types=1);

namespace App\FileStorage\Presentation\Http\Controller;

use App\FileStorage\Application\UseCase\ListStoreMediaLibraryUseCase;
use App\FileStorage\Application\UseCase\UploadStoreMediaFileUseCase;
use App\FileStorage\Application\UseCase\DeleteStoreMediaFileUseCase;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;
use App\Tenant\Application\UseCase\ShowStoreSettingsUseCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use InvalidArgumentException;

#[Route('/owner/media')]
final readonly class OwnerMediaLibraryController
{
    public function __construct(
        private Environment $twig,
        private ShowStoreSettingsUseCase $showStoreSettings,
        private ListStoreMediaLibraryUseCase $listMediaLibrary,
        private UploadStoreMediaFileUseCase $uploadMediaFile,
        private DeleteStoreMediaFileUseCase $deleteMediaFile,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/upload', name: 'owner_media_upload', methods: ['POST'])]
    public function upload(Request $request): Response
    {
        $email = $this->ownerEmail($request);
        if ($email === null) { return new RedirectResponse('/auth/login'); }
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('owner_media', $request->request->getString('_token')))) {
            return new RedirectResponse('/owner/media?error=stale');
        }
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile || !$file->isValid()) { return new RedirectResponse('/owner/media?error=file'); }
        try {
            $contents = file_get_contents($file->getPathname());
            $this->uploadMediaFile->execute($email, $file->getClientOriginalName(), (string) $file->getMimeType(), $file->getSize(), is_string($contents) ? $contents : '');
        } catch (InvalidArgumentException) { return new RedirectResponse('/owner/media?error=file'); }
        return new RedirectResponse('/owner/media?notice=uploaded');
    }

    #[Route('/{fileId}/delete', name: 'owner_media_delete', methods: ['POST'])]
    public function delete(Request $request, string $fileId): Response
    {
        $email = $this->ownerEmail($request);
        if ($email === null) { return new RedirectResponse('/auth/login'); }
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('owner_media', $request->request->getString('_token')))) {
            return new RedirectResponse('/owner/media?error=stale');
        }
        return new RedirectResponse('/owner/media?'.($this->deleteMediaFile->execute($email, $fileId) ? 'notice=deleted' : 'error=used'));
    }

    #[Route('', name: 'owner_media_library', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $ownerEmail = $this->ownerEmail($request);
        if ($ownerEmail === null) {
            return new RedirectResponse('/auth/login');
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
