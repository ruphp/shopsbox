<?php

declare(strict_types=1);

namespace App\Sales\Presentation\Http\Controller;

use App\Sales\Application\Dto\CartLineInput;
use App\Sales\Application\Dto\CheckoutInput;
use App\Sales\Application\Exception\InvalidCheckout;
use App\Sales\Application\UseCase\BuildCartUseCase;
use App\Sales\Application\UseCase\CheckoutUseCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;

final readonly class StorefrontCartController
{
    public function __construct(
        private Environment $twig,
        private BuildCartUseCase $buildCart,
        private CheckoutUseCase $checkout,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/s/{storeSlug}/cart', name: 'storefront_cart', methods: ['GET'])]
    #[Route('/cart', name: 'host_storefront_cart', host: '{storeSlug}.shopsbox.ru', methods: ['GET'])]
    #[Route('/cart', name: 'local_host_storefront_cart', host: '{storeSlug}.shopsbox.local', methods: ['GET'])]
    public function show(Request $request, string $storeSlug): Response
    {
        return $this->renderCart($request, $storeSlug);
    }

    #[Route('/s/{storeSlug}/cart/add', name: 'storefront_cart_add', methods: ['POST'])]
    #[Route('/cart/add', name: 'host_storefront_cart_add', host: '{storeSlug}.shopsbox.ru', methods: ['POST'])]
    #[Route('/cart/add', name: 'local_host_storefront_cart_add', host: '{storeSlug}.shopsbox.local', methods: ['POST'])]
    public function add(Request $request, string $storeSlug): Response
    {
        $this->requireValidToken($request, 'cart_add');
        $productId = trim((string) $request->request->get('product_id'));
        $variantId = trim((string) $request->request->get('variant_id')) ?: null;
        $quantity = max(1, min(99, $request->request->getInt('quantity', 1)));
        if ($productId !== '') {
            $lines = $this->sessionLines($request, $storeSlug);
            $key = $productId . ':' . ($variantId ?? 'base');
            $lines[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => min(99, ($lines[$key]['quantity'] ?? 0) + $quantity),
            ];
            $request->getSession()->set($this->sessionKey($storeSlug), $lines);
        }

        return new RedirectResponse($this->cartPath($request, $storeSlug));
    }

    #[Route('/s/{storeSlug}/cart/update', name: 'storefront_cart_update', methods: ['POST'])]
    #[Route('/cart/update', name: 'host_storefront_cart_update', host: '{storeSlug}.shopsbox.ru', methods: ['POST'])]
    #[Route('/cart/update', name: 'local_host_storefront_cart_update', host: '{storeSlug}.shopsbox.local', methods: ['POST'])]
    public function update(Request $request, string $storeSlug): Response
    {
        $this->requireValidToken($request, 'cart_update');
        $quantities = $request->request->all('quantity');
        $lines = $this->sessionLines($request, $storeSlug);
        foreach ($lines as $key => $line) {
            $quantity = max(0, min(99, (int) ($quantities[$key] ?? $line['quantity'])));
            if ($quantity === 0) {
                unset($lines[$key]);
            } else {
                $lines[$key]['quantity'] = $quantity;
            }
        }
        $request->getSession()->set($this->sessionKey($storeSlug), $lines);

        return new RedirectResponse($this->cartPath($request, $storeSlug));
    }

    #[Route('/s/{storeSlug}/checkout', name: 'storefront_checkout', methods: ['POST'])]
    #[Route('/checkout', name: 'host_storefront_checkout', host: '{storeSlug}.shopsbox.ru', methods: ['POST'])]
    #[Route('/checkout', name: 'local_host_storefront_checkout', host: '{storeSlug}.shopsbox.local', methods: ['POST'])]
    public function checkout(Request $request, string $storeSlug): Response
    {
        $this->requireValidToken($request, 'checkout');
        $customer = [
            'name' => trim((string) $request->request->get('customer_name')),
            'email' => trim((string) $request->request->get('customer_email')),
            'phone' => trim((string) $request->request->get('customer_phone')),
            'comment' => trim((string) $request->request->get('comment')),
        ];

        try {
            $order = $this->checkout->execute(new CheckoutInput(
                $storeSlug,
                $this->lineInputs($request, $storeSlug),
                $customer['name'],
                $customer['email'],
                $customer['phone'],
                $customer['comment'],
            ));
        } catch (InvalidCheckout $exception) {
            return $this->renderCart($request, $storeSlug, $exception->getMessage(), $customer);
        }

        $request->getSession()->remove($this->sessionKey($storeSlug));

        $emptyCart = $this->buildCart->execute($storeSlug, []);
        if ($emptyCart === null) {
            return new Response('Store not found.', Response::HTTP_NOT_FOUND);
        }

        return new Response($this->twig->render('storefront/order_confirmation.html.twig', [
            'store' => $emptyCart->store,
            'order' => $order,
        ]));
    }

    /** @param array{name: string, email: string, phone: string, comment: string} $customer */
    private function renderCart(Request $request, string $storeSlug, ?string $error = null, array $customer = []): Response
    {
        $cart = $this->buildCart->execute($storeSlug, $this->lineInputs($request, $storeSlug));
        if ($cart === null) {
            return new Response('Store not found.', Response::HTTP_NOT_FOUND);
        }

        return new Response($this->twig->render('storefront/cart.html.twig', [
            'store' => $cart->store,
            'cart' => $cart,
            'checkoutError' => $error,
            'customer' => $customer,
        ]), $error === null ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @return list<CartLineInput> */
    private function lineInputs(Request $request, string $storeSlug): array
    {
        return array_values(array_map(
            static fn (array $line): CartLineInput => new CartLineInput($line['product_id'], $line['variant_id'], $line['quantity']),
            $this->sessionLines($request, $storeSlug),
        ));
    }

    /** @return array<string, array{product_id: string, variant_id: ?string, quantity: int}> */
    private function sessionLines(Request $request, string $storeSlug): array
    {
        $raw = $request->getSession()->get($this->sessionKey($storeSlug), []);
        if (!is_array($raw)) {
            return [];
        }

        $lines = [];
        foreach ($raw as $key => $line) {
            if (!is_string($key) || !is_array($line) || !is_string($line['product_id'] ?? null)) {
                continue;
            }
            $variantId = $line['variant_id'] ?? null;
            $lines[$key] = [
                'product_id' => $line['product_id'],
                'variant_id' => is_string($variantId) && $variantId !== '' ? $variantId : null,
                'quantity' => max(1, min(99, (int) ($line['quantity'] ?? 1))),
            ];
        }

        return $lines;
    }

    private function requireValidToken(Request $request, string $id): void
    {
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken($id, (string) $request->request->get('_token')))) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Invalid CSRF token.');
        }
    }

    private function sessionKey(string $storeSlug): string
    {
        return 'shopsbox_cart_' . strtolower($storeSlug);
    }

    private function cartPath(Request $request, string $storeSlug): string
    {
        return str_starts_with((string) $request->attributes->get('_route'), 'storefront_')
            ? '/s/' . rawurlencode($storeSlug) . '/cart'
            : '/cart';
    }
}
