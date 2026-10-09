<?php

declare(strict_types=1);

namespace App\Tests\Tenant\Domain;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class StoreTrialTest extends TestCase
{
    public function testItStartsTrialOnceForTenDays(): void
    {
        $store = $this->store();
        $startedAt = new DateTimeImmutable('2026-10-08 12:00:00');

        $store->startTrial(10, $startedAt);
        $store->startTrial(10, new DateTimeImmutable('2026-10-09 12:00:00'));

        self::assertSame('trial', $store->plan());
        self::assertSame($startedAt, $store->trialStartedAt());
        self::assertSame('2026-10-18 12:00:00', $store->planExpiresAt()?->format('Y-m-d H:i:s'));
        self::assertTrue($store->trialActive(new DateTimeImmutable('2026-10-18 11:59:59')));
        self::assertFalse($store->trialActive(new DateTimeImmutable('2026-10-18 12:00:00')));
    }

    private function store(): Store
    {
        $tenant = new Tenant(
            '11111111-1111-4111-8111-111111111111',
            'Demo tenant',
            'active',
            'owner@example.test',
        );

        return new Store(
            '22222222-2222-4222-8222-222222222222',
            $tenant,
            'Demo store',
            'demo-store',
            'demo-store.shopsbox.ru',
            'draft',
            'RUB',
            'Asia/Yekaterinburg',
        );
    }
}
