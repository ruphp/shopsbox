<?php

declare(strict_types=1);

namespace App\Tests\Storefront\Presentation\Http;

use App\Storefront\Application\Contracts\StorefrontProductRepository;
use App\Storefront\Application\UseCase\RecordStorefrontProductViewUseCase;
use App\Storefront\Presentation\Http\ProductViewSessionRecorder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class ProductViewSessionRecorderTest extends TestCase
{
    public function testItCountsAProductOnlyOncePerSession(): void
    {
        $repository = $this->createMock(StorefrontProductRepository::class);
        $repository->expects(self::once())->method('incrementViewCount')->with('product-id');
        $recorder = new ProductViewSessionRecorder(new RecordStorefrontProductViewUseCase($repository));
        $request = $this->request('Mozilla/5.0');

        $recorder->record($request, 'product-id');
        $recorder->record($request, 'product-id');

        self::assertSame(['product-id'], $request->getSession()->get('storefront_viewed_products'));
    }

    public function testItDoesNotCountRobots(): void
    {
        $repository = $this->createMock(StorefrontProductRepository::class);
        $repository->expects(self::never())->method('incrementViewCount');
        $recorder = new ProductViewSessionRecorder(new RecordStorefrontProductViewUseCase($repository));

        $recorder->record($this->request('ExampleBot/1.0'), 'product-id');
    }

    private function request(string $userAgent): Request
    {
        $request = Request::create('/product');
        $request->headers->set('User-Agent', $userAgent);
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }
}
