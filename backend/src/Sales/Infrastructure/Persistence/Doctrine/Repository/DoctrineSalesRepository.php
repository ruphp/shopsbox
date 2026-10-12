<?php

declare(strict_types=1);

namespace App\Sales\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\ProductPublicationStatus;
use App\Catalog\Domain\ProductStatus;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Product;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductImage;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductVariant;
use App\Sales\Application\Contracts\SalesRepository;
use App\Sales\Application\Dto\CartItemView;
use App\Sales\Application\Dto\CartLineInput;
use App\Sales\Application\Dto\CartView;
use App\Sales\Application\Dto\CheckoutInput;
use App\Sales\Application\Dto\OrderItemView;
use App\Sales\Application\Dto\OrderView;
use App\Sales\Infrastructure\Persistence\Doctrine\Entity\CustomerOrder;
use App\Sales\Infrastructure\Persistence\Doctrine\Entity\CustomerOrderItem;
use App\Storefront\Application\Dto\StorefrontStoreView;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineSalesRepository implements SalesRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function buildCart(string $storeSlug, array $lines): ?CartView
    {
        $store = $this->entityManager->getRepository(Store::class)->findOneBy(['slug' => $storeSlug, 'status' => 'active']);
        if (!$store instanceof Store) {
            return null;
        }

        $items = [];
        $total = 0;
        foreach ($lines as $line) {
            if (!$line instanceof CartLineInput || $line->quantity < 1 || $line->quantity > 99) {
                continue;
            }

            $product = $this->entityManager->createQueryBuilder()
                ->select('product')
                ->from(Product::class, 'product')
                ->where('product.id = :productId')
                ->andWhere('IDENTITY(product.store) = :storeId')
                ->andWhere('product.status = :status')
                ->andWhere('product.publicationStatus = :publicationStatus')
                ->setParameter('productId', $line->productId)
                ->setParameter('storeId', $store->id())
                ->setParameter('status', ProductStatus::ACTIVE)
                ->setParameter('publicationStatus', ProductPublicationStatus::PUBLISHED)
                ->getQuery()
                ->getOneOrNullResult();
            if (!$product instanceof Product) {
                continue;
            }

            $activeVariantCount = (int) $this->entityManager->createQueryBuilder()
                ->select('COUNT(variant.id)')
                ->from(ProductVariant::class, 'variant')
                ->where('variant.product = :product')
                ->andWhere('variant.active = true')
                ->setParameter('product', $product)
                ->getQuery()
                ->getSingleScalarResult();

            $variant = null;
            if ($line->variantId !== null) {
                $variant = $this->entityManager->getRepository(ProductVariant::class)->findOneBy([
                    'id' => $line->variantId,
                    'product' => $product,
                    'active' => true,
                ]);
                if (!$variant instanceof ProductVariant) {
                    continue;
                }
            } elseif ($activeVariantCount > 0) {
                continue;
            }

            $unitPrice = $this->toMinor($product->price())
                + ($variant instanceof ProductVariant ? $this->toMinor($variant->priceAdjustment() ?? '0.00') : 0);
            $unitPrice = max(0, $unitPrice);
            $lineTotal = $unitPrice * $line->quantity;
            $key = $product->id() . ':' . ($variant instanceof ProductVariant ? $variant->id() : 'base');
            $items[] = new CartItemView(
                $key,
                $product->id(),
                $variant?->id(),
                $product->name(),
                $product->slug(),
                $variant?->name(),
                $this->firstImageUrl($product),
                $unitPrice,
                $line->quantity,
                $lineTotal,
            );
            $total += $lineTotal;
        }

        return new CartView(
            new StorefrontStoreView(
                $store->id(),
                $store->tenant()->id(),
                $store->name(),
                $store->slug(),
                $store->domain(),
                $store->themeSettings(),
                $store->defaultCurrency(),
                $store->contactEmail(),
                $store->contactPhone(),
                $store->contactCity(),
                $store->contactAddress(),
            ),
            $items,
            $total,
            $store->defaultCurrency(),
        );
    }

    public function create(string $id, string $number, CheckoutInput $input, CartView $cart): OrderView
    {
        $tenant = $this->entityManager->getReference(Tenant::class, $cart->store->tenantId);
        $store = $this->entityManager->getReference(Store::class, $cart->store->id);
        $order = new CustomerOrder(
            $id,
            $tenant,
            $store,
            $number,
            $input->customerName,
            $input->customerEmail,
            $input->customerPhone,
            $input->comment,
            $cart->totalMinor,
            $cart->currency,
        );
        foreach ($cart->items as $position => $item) {
            $order->addItem(new CustomerOrderItem(
                Uuid::v7()->toRfc4122(),
                $order,
                $item->productId,
                $item->variantId,
                $item->productName,
                $item->variantName,
                $item->unitPriceMinor,
                $item->quantity,
                $position,
            ));
        }
        $this->entityManager->persist($order);

        return $this->mapOrder($order);
    }

    public function listByOwnerEmail(string $ownerEmail): array
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $ownerEmail]);
        if (!$user instanceof User || !$user->tenant() instanceof Tenant) {
            return [];
        }

        $orders = $this->entityManager->createQueryBuilder()
            ->select('customerOrder', 'items')
            ->from(CustomerOrder::class, 'customerOrder')
            ->leftJoin('customerOrder.items', 'items')
            ->where('IDENTITY(customerOrder.tenant) = :tenantId')
            ->setParameter('tenantId', $user->tenant()->id())
            ->orderBy('customerOrder.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(fn (CustomerOrder $order): OrderView => $this->mapOrder($order), $orders);
    }

    private function mapOrder(CustomerOrder $order): OrderView
    {
        return new OrderView(
            $order->id(),
            $order->number(),
            $order->status(),
            $order->customerName(),
            $order->customerEmail(),
            $order->customerPhone(),
            $order->comment(),
            $order->totalMinor(),
            $order->currency(),
            $order->createdAt()->format(DATE_ATOM),
            array_map(static fn (CustomerOrderItem $item): OrderItemView => new OrderItemView(
                $item->productName(),
                $item->variantName(),
                $item->unitPriceMinor(),
                $item->quantity(),
                $item->lineTotalMinor(),
            ), $order->items()),
        );
    }

    private function firstImageUrl(Product $product): ?string
    {
        $image = $this->entityManager->createQueryBuilder()
            ->select('image')
            ->from(ProductImage::class, 'image')
            ->where('image.product = :product')
            ->andWhere('image.deletedAt IS NULL')
            ->setParameter('product', $product)
            ->orderBy('image.primaryImage', 'DESC')
            ->addOrderBy('image.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $image instanceof ProductImage ? $image->publicUrl() : null;
    }

    private function toMinor(string $amount): int
    {
        $negative = str_starts_with($amount, '-');
        $amount = ltrim($amount, '+-');
        [$major, $minor] = array_pad(explode('.', $amount, 2), 2, '0');
        $value = ((int) $major * 100) + (int) str_pad(substr($minor, 0, 2), 2, '0');

        return $negative ? -$value : $value;
    }
}
