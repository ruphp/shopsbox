<?php

declare(strict_types=1);

namespace App\Moderation\Presentation\Http\Controller;

use App\Moderation\Application\UseCase\ManageModeratorsUseCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/platform/moderators')]
final readonly class PlatformModeratorController
{
    public function __construct(
        private Environment $twig,
        private ManageModeratorsUseCase $moderators,
    ) {
    }

    #[Route('', name: 'platform_moderators', methods: ['GET'])]
    public function list(Request $request): Response
    {
        return new Response($this->twig->render('moderation/moderators.html.twig', [
            'moderators' => $this->moderators->list(),
            'error' => $request->query->get('error'),
        ]));
    }

    #[Route('/assign', name: 'platform_moderator_assign', methods: ['POST'])]
    public function assign(Request $request): Response
    {
        return $this->handle($request, true);
    }

    #[Route('/remove', name: 'platform_moderator_remove', methods: ['POST'])]
    public function remove(Request $request): Response
    {
        return $this->handle($request, false);
    }

    private function handle(Request $request, bool $assign): Response
    {
        try {
            $assign
                ? $this->moderators->assign((string) $request->request->get('email', ''))
                : $this->moderators->remove((string) $request->request->get('email', ''));
        } catch (RuntimeException $exception) {
            return new RedirectResponse('/platform/moderators?error=' . rawurlencode($exception->getMessage()));
        }

        return new RedirectResponse('/platform/moderators');
    }
}
