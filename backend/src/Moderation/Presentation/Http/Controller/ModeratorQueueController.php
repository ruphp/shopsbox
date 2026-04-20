<?php

declare(strict_types=1);

namespace App\Moderation\Presentation\Http\Controller;

use App\Moderation\Application\UseCase\ListModerationQueueUseCase;
use App\Moderation\Application\UseCase\ReviewModerationItemUseCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/moderator')]
final readonly class ModeratorQueueController
{
    public function __construct(
        private Environment $twig,
        private ListModerationQueueUseCase $listQueue,
        private ReviewModerationItemUseCase $reviewItem,
    ) {
    }

    #[Route('', name: 'moderator_queue', methods: ['GET'])]
    public function queue(Request $request): Response
    {
        return new Response($this->twig->render('moderation/queue.html.twig', [
            'queue' => $this->listQueue->execute(),
            'error' => $request->query->get('error'),
        ]));
    }

    #[Route('/{type}/{id}/review', name: 'moderator_review', methods: ['POST'])]
    public function review(Request $request, string $type, string $id): Response
    {
        try {
            $this->reviewItem->execute(
                $type,
                $id,
                (string) $request->request->get('decision', ''),
                (string) $request->request->get('reason', ''),
                'dev-moderator',
            );
        } catch (RuntimeException $exception) {
            return new RedirectResponse('/moderator?error=' . rawurlencode($exception->getMessage()));
        }

        return new RedirectResponse('/moderator');
    }
}
