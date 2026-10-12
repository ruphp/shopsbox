<?php

declare(strict_types=1);

namespace App\Sales\Application\UseCase;

use App\Sales\Application\Contracts\EntityFlusher;
use App\Sales\Application\Contracts\SalesRepository;
use App\Sales\Application\Contracts\UuidGenerator;
use App\Sales\Application\Dto\CheckoutInput;
use App\Sales\Application\Dto\OrderView;
use App\Sales\Application\Exception\InvalidCheckout;

final readonly class CheckoutUseCase
{
    public function __construct(
        private SalesRepository $salesRepository,
        private EntityFlusher $entityFlusher,
        private UuidGenerator $uuidGenerator,
    ) {
    }

    public function execute(CheckoutInput $input): OrderView
    {
        $name = trim($input->customerName);
        $email = strtolower(trim($input->customerEmail));
        $phone = $this->normalizePhone($input->customerPhone);
        $comment = trim((string) $input->comment);

        if ($name === '' || mb_strlen($name) > 120) {
            throw new InvalidCheckout('customer_name', 'Укажите имя покупателя, не более 120 символов.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidCheckout('customer_email', 'Укажите корректный email.');
        }
        if (!preg_match('/^\+79\d{9}$/', $phone)) {
            throw new InvalidCheckout('customer_phone', 'Укажите мобильный номер РФ в формате +79XXXXXXXXX.');
        }
        if (mb_strlen($comment) > 2000) {
            throw new InvalidCheckout('comment', 'Комментарий не должен превышать 2000 символов.');
        }

        $cart = $this->salesRepository->buildCart($input->storeSlug, $input->lines);
        if ($cart === null) {
            throw new InvalidCheckout('cart', 'Магазин не найден.');
        }
        if ($cart->items === []) {
            throw new InvalidCheckout('cart', 'Корзина пуста или товары больше недоступны.');
        }

        $id = $this->uuidGenerator->generate();
        $normalized = new CheckoutInput(
            $input->storeSlug,
            $input->lines,
            $name,
            $email,
            $phone,
            $comment === '' ? null : $comment,
        );
        $order = $this->salesRepository->create(
            $id,
            (new \DateTimeImmutable())->format('ymd') . '-' . strtoupper(substr(str_replace('-', '', $id), 0, 8)),
            $normalized,
            $cart,
        );
        $this->entityFlusher->flush();

        return $order;
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) === 11 && str_starts_with($digits, '8')) {
            $digits = '7' . substr($digits, 1);
        }

        return $digits === '' ? '' : '+' . $digits;
    }
}
