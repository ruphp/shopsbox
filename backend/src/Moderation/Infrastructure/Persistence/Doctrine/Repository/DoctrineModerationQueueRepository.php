<?php

declare(strict_types=1);

namespace App\Moderation\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\ProductPublicationStatus;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Product;
use App\Moderation\Application\Contracts\ModerationQueueRepository;
use App\Moderation\Application\Dto\ModerationProductView;
use App\Moderation\Application\Dto\ModerationStoreView;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineModerationQueueRepository implements ModerationQueueRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function pendingProducts(): array
    {
        $products = $this->entityManager->createQueryBuilder()
            ->select('product', 'store')
            ->from(Product::class, 'product')
            ->join('product.store', 'store')
            ->where('product.publicationStatus = :status')
            ->setParameter('status', ProductPublicationStatus::PENDING_REVIEW)
            ->orderBy('product.updatedAt', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(fn (Product $product): ModerationProductView => new ModerationProductView(
            $product->id(),
            $product->store()->id(),
            $product->store()->name(),
            $product->name(),
            $product->slug(),
            $product->publicationStatus()->value,
            'Первая публикация или изменение карточки товара.',
            $product->publicationReviewReason(),
            $product->publicationSubmittedAt()?->format('Y-m-d H:i'),
        ), $products);
    }

    public function pendingStores(): array
    {
        $stores = $this->entityManager->createQueryBuilder()
            ->select('store')
            ->from(Store::class, 'store')
            ->where('store.publicationStatus = :status')
            ->setParameter('status', 'pending_review')
            ->orderBy('store.updatedAt', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(fn (Store $store): ModerationStoreView => new ModerationStoreView(
            $store->id(),
            $store->name(),
            $store->publicSubdomain(),
            $store->publicationStatus(),
            'Публикация магазина на поддомене ShopsBox.',
            $store->publicationReviewReason(),
            $store->publicationTermsAcceptedAt()?->format('Y-m-d H:i'),
        ), $stores);
    }

    public function reviewProduct(string $productId, string $decision, string $reason, ?string $reviewedBy): bool
    {
        $product = $this->entityManager->find(Product::class, $productId);
        if (!$product instanceof Product) {
            return false;
        }

        match ($decision) {
            'approve' => $product->approvePublication($reviewedBy),
            'reject' => $product->rejectPublication($reviewedBy, $reason),
            'block' => $product->blockPublication($reviewedBy, $reason),
            default => null,
        };

        return true;
    }

    public function reviewStore(string $storeId, string $decision, string $reason, ?string $reviewedBy): bool
    {
        $store = $this->entityManager->find(Store::class, $storeId);
        if (!$store instanceof Store) {
            return false;
        }

        match ($decision) {
            'approve' => $store->approvePublication($reviewedBy),
            'reject' => $store->rejectPublication($reviewedBy, $reason),
            'block' => $store->blockPublication($reviewedBy, $reason),
            default => null,
        };

        return true;
    }
}
