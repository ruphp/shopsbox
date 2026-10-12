<?php

declare(strict_types=1);

namespace App\Tests\Sales\Application\UseCase;

use App\Sales\Application\Contracts\EntityFlusher;
use App\Sales\Application\Contracts\SalesRepository;
use App\Sales\Application\Contracts\UuidGenerator;
use App\Sales\Application\Dto\CartItemView;
use App\Sales\Application\Dto\CartLineInput;
use App\Sales\Application\Dto\CartView;
use App\Sales\Application\Dto\CheckoutInput;
use App\Sales\Application\Dto\OrderView;
use App\Sales\Application\Exception\InvalidCheckout;
use App\Sales\Application\UseCase\CheckoutUseCase;
use App\Storefront\Application\Dto\StorefrontStoreView;
use PHPUnit\Framework\TestCase;

final class CheckoutUseCaseTest extends TestCase
{
    public function testCreatesOrderWithNormalizedCustomerData(): void
    {
        $repository = new CheckoutSalesRepository($this->cart());
        $flusher = new CheckoutFlusher();
        $useCase = new CheckoutUseCase($repository, $flusher, new CheckoutUuidGenerator());

        $order = $useCase->execute(new CheckoutInput(
            'demo-store',
            [new CartLineInput('product-id', null, 2)],
            '  Иван  ',
            ' IVAN@EXAMPLE.RU ',
            '8 (912) 345-67-89',
            ' Позвонить вечером ',
        ));

        self::assertSame('Иван', $order->customerName);
        self::assertSame('ivan@example.ru', $order->customerEmail);
        self::assertSame('+79123456789', $order->customerPhone);
        self::assertSame('Позвонить вечером', $order->comment);
        self::assertSame(1, $flusher->calls);
        self::assertNotNull($repository->receivedInput);
    }

    public function testRejectsInvalidEmailBeforeCreatingOrder(): void
    {
        $repository = new CheckoutSalesRepository($this->cart());
        $useCase = new CheckoutUseCase($repository, new CheckoutFlusher(), new CheckoutUuidGenerator());

        $this->expectException(InvalidCheckout::class);
        $this->expectExceptionMessage('Укажите корректный email.');
        $useCase->execute(new CheckoutInput('demo-store', [], 'Иван', 'not-email', '+79123456789', null));
    }

    public function testRejectsEmptyCart(): void
    {
        $repository = new CheckoutSalesRepository(new CartView($this->store(), [], 0, 'RUB'));
        $useCase = new CheckoutUseCase($repository, new CheckoutFlusher(), new CheckoutUuidGenerator());

        $this->expectException(InvalidCheckout::class);
        $this->expectExceptionMessage('Корзина пуста');
        $useCase->execute(new CheckoutInput('demo-store', [], 'Иван', 'ivan@example.ru', '+79123456789', null));
    }

    private function cart(): CartView
    {
        return new CartView($this->store(), [
            new CartItemView('product-id:base', 'product-id', null, 'Товар', 'product', null, null, 12500, 2, 25000),
        ], 25000, 'RUB');
    }

    private function store(): StorefrontStoreView
    {
        return new StorefrontStoreView('store-id', 'tenant-id', 'Магазин', 'demo-store', 'demo-store.shopsbox.ru', [], 'RUB');
    }
}

final class CheckoutSalesRepository implements SalesRepository
{
    public ?CheckoutInput $receivedInput = null;

    public function __construct(private readonly ?CartView $cart)
    {
    }

    public function buildCart(string $storeSlug, array $lines): ?CartView
    {
        return $this->cart;
    }

    public function create(string $id, string $number, CheckoutInput $input, CartView $cart): OrderView
    {
        $this->receivedInput = $input;

        return new OrderView($id, $number, 'new', $input->customerName, $input->customerEmail, $input->customerPhone, $input->comment, $cart->totalMinor, $cart->currency, '2026-10-12T12:00:00+00:00', []);
    }

    public function listByOwnerEmail(string $ownerEmail): array
    {
        return [];
    }
}

final class CheckoutFlusher implements EntityFlusher
{
    public int $calls = 0;

    public function flush(): void
    {
        ++$this->calls;
    }
}

final class CheckoutUuidGenerator implements UuidGenerator
{
    public function generate(): string
    {
        return '11111111-1111-4111-8111-111111111111';
    }
}
