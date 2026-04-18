<?php

declare(strict_types=1);

namespace App\Tests\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Application\Contracts\TenantRepository;
use App\Tenant\Application\Contracts\UuidGenerator;
use App\Tenant\Application\Dto\CreateTenantInput;
use App\Tenant\Application\UseCase\CreateTenantUseCase;
use App\Tenant\Application\Exception\InvalidTenantInput;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CreateTenantUseCaseTest extends TestCase
{
    public function testItCreatesTenantAndStore(): void
    {
        $tenantRepository = new FakeTenantRepository();
        $storeRepository = new FakeStoreRepository();
        $entityFlusher = new SpyEntityFlusher();
        $useCase = new CreateTenantUseCase(
            $tenantRepository,
            $storeRepository,
            $entityFlusher,
            new StubListUuidGenerator([
                '11111111-1111-4111-8111-111111111111',
                '22222222-2222-4222-8222-222222222222',
            ]),
        );

        $result = $useCase->execute($this->validInput());

        self::assertSame('11111111-1111-4111-8111-111111111111', $result->tenantId);
        self::assertSame('22222222-2222-4222-8222-222222222222', $result->storeId);
        self::assertCount(1, $tenantRepository->saved);
        self::assertCount(1, $storeRepository->saved);
        self::assertSame('Demo Tenant', $tenantRepository->saved[0]['name']);
        self::assertSame('billing@demo.shopsbox.local', $tenantRepository->saved[0]['billingEmail']);
        self::assertSame('Demo Store', $storeRepository->saved[0]['name']);
        self::assertSame('demo-store', $storeRepository->saved[0]['slug']);
        self::assertSame('demo.shopsbox.local', $storeRepository->saved[0]['domain']);
        self::assertTrue($entityFlusher->flushed);
    }

    #[DataProvider('invalidInputCases')]
    public function testItRejectsInvalidInput(CreateTenantInput $input, string $expectedField): void
    {
        $this->expectException(InvalidTenantInput::class);

        try {
            $this->useCase()->execute($input);
        } catch (InvalidTenantInput $exception) {
            self::assertSame($expectedField, $exception->field);
            throw $exception;
        }
    }

    /**
     * @return iterable<string, array{CreateTenantInput, string}>
     */
    public static function invalidInputCases(): iterable
    {
        yield 'invalid email' => [
            self::input(billingEmail: 'not-email'),
            'billing_email',
        ];
        yield 'invalid slug' => [
            self::input(storeSlug: 'Demo Store'),
            'store_slug',
        ];
        yield 'invalid domain' => [
            self::input(storeDomain: 'demo store local'),
            'store_domain',
        ];
        yield 'invalid currency' => [
            self::input(defaultCurrency: 'rub'),
            'default_currency',
        ];
        yield 'invalid timezone' => [
            self::input(timezone: 'Local/Nowhere'),
            'timezone',
        ];
    }

    public function testItRejectsAlreadyUsedDomain(): void
    {
        $storeRepository = new FakeStoreRepository(['demo.shopsbox.local']);
        $useCase = new CreateTenantUseCase(
            new FakeTenantRepository(),
            $storeRepository,
            new SpyEntityFlusher(),
            new StubListUuidGenerator([
                '11111111-1111-4111-8111-111111111111',
                '22222222-2222-4222-8222-222222222222',
            ]),
        );

        try {
            $useCase->execute($this->validInput());
            self::fail('Expected duplicate domain validation error.');
        } catch (InvalidTenantInput $exception) {
            self::assertSame('store_domain', $exception->field);
            self::assertSame('Store domain is already used.', $exception->getMessage());
        }
    }

    private function useCase(): CreateTenantUseCase
    {
        return new CreateTenantUseCase(
            new FakeTenantRepository(),
            new FakeStoreRepository(),
            new SpyEntityFlusher(),
            new StubListUuidGenerator([
                '11111111-1111-4111-8111-111111111111',
                '22222222-2222-4222-8222-222222222222',
            ]),
        );
    }

    private function validInput(): CreateTenantInput
    {
        return self::input();
    }

    private static function input(
        string $tenantName = 'Demo Tenant',
        string $billingEmail = 'billing@demo.shopsbox.local',
        string $storeName = 'Demo Store',
        string $storeSlug = 'demo-store',
        string $storeDomain = 'demo.shopsbox.local',
        string $defaultCurrency = 'RUB',
        string $timezone = 'Asia/Yekaterinburg',
    ): CreateTenantInput {
        return new CreateTenantInput(
            $tenantName,
            $billingEmail,
            $storeName,
            $storeSlug,
            $storeDomain,
            $defaultCurrency,
            $timezone,
        );
    }
}

final class FakeTenantRepository implements TenantRepository
{
    /**
     * @var list<array{id: string, name: string, status: string, billingEmail: string}>
     */
    public array $saved = [];

    public function persist(string $id, string $name, string $status, string $billingEmail): void
    {
        $this->saved[] = [
            'id' => $id,
            'name' => $name,
            'status' => $status,
            'billingEmail' => $billingEmail,
        ];
    }
}

final class FakeStoreRepository implements StoreRepository
{
    /**
     * @var list<array{id: string, tenantId: string, name: string, slug: string, domain: string, status: string, defaultCurrency: string, timezone: string}>
     */
    public array $saved = [];

    /**
     * @param list<string> $existingDomains
     */
    public function __construct(private readonly array $existingDomains = [])
    {
    }

    public function existsByDomain(string $domain): bool
    {
        return in_array($domain, $this->existingDomains, true);
    }

    public function persist(
        string $id,
        string $tenantId,
        string $name,
        string $slug,
        string $domain,
        string $status,
        string $defaultCurrency,
        string $timezone,
    ): void
    {
        $this->saved[] = [
            'id' => $id,
            'tenantId' => $tenantId,
            'name' => $name,
            'slug' => $slug,
            'domain' => $domain,
            'status' => $status,
            'defaultCurrency' => $defaultCurrency,
            'timezone' => $timezone,
        ];
    }
}

final class SpyEntityFlusher implements EntityFlusher
{
    public bool $flushed = false;

    public function flush(): void
    {
        $this->flushed = true;
    }
}

final class StubListUuidGenerator implements UuidGenerator
{
    /**
     * @param list<string> $uuids
     */
    public function __construct(private array $uuids)
    {
    }

    public function generate(): string
    {
        $uuid = array_shift($this->uuids);
        if (!is_string($uuid)) {
            throw new RuntimeException('No UUID left in test generator.');
        }

        return $uuid;
    }
}
