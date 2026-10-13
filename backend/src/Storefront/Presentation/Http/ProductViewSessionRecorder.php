<?php

declare(strict_types=1);

namespace App\Storefront\Presentation\Http;

use App\Storefront\Application\UseCase\RecordStorefrontProductViewUseCase;
use Symfony\Component\HttpFoundation\Request;

final readonly class ProductViewSessionRecorder
{
    public function __construct(private RecordStorefrontProductViewUseCase $recordProductView)
    {
    }

    public function record(Request $request, string $productId): void
    {
        if (preg_match('/bot|crawler|spider|slurp/i', $request->headers->get('User-Agent', ''))) {
            return;
        }

        $session = $request->getSession();
        $viewed = array_values(array_map('strval', (array) $session->get('storefront_viewed_products', [])));
        if (in_array($productId, $viewed, true)) {
            return;
        }

        $this->recordProductView->execute($productId);
        $viewed[] = $productId;
        $session->set('storefront_viewed_products', array_slice($viewed, -200));
    }
}
