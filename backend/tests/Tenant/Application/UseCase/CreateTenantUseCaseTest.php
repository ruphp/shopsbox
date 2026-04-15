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
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CreateTenantUseCaseTest extends TestCase
{
    public function testItCreatesTenantAndStore(): void
    {
        $tenantRepository = new InMemoryTenantRepository();
        $storeRepository = new InMemoryStoreRepository();
        $entityFlusher = new SpyEntityFlusher();
        $useCase = new CreateTenantUseCase(
            $tenantRepository,
            $storeRepository,
            $entityFlusher,
            new SequentialUuidGenerator([
                '11111111-1111-4111-8111-111111111111',
                '22222222-2222-4222-8222-222222222222',
            ]),
        );

        $result = $useCase->execute($this->validInput());

        self::assertSame('11111111-1111-4111-8111-111111111111', $result->tenantId);
        self::assertSame('22222222-2222-4222-8222-222222222222', $result->storeId);
        self::assertCount(1, $tenantRepository->saved);
        self::assertCount(1, $storeRepository->saved);
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
        $storeRepository = new InMemoryStoreRepository(['demo.shopsbox.local']);
        $useCase = new CreateTenantUseCase(
            new InMemoryTenantRepository(),
            $storeRepository,
            new SpyEntityFlusher(),
            new SequentialUuidGenerator([
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
            new InMemoryTenantRepository(),
            new InMemoryStoreRepository(),
            new SpyEntityFlusher(),
            new SequentialUuidGenerator([
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

final class InMemoryTenantRepository implements TenantRepository
{
    /**
     * @var list<Tenant>
     */
    public array $saved = [];

    public function persist(Tenant $tenant): void
    {
        $this->saved[] = $tenant;
    }
}

final class InMemoryStoreRepository implements StoreRepository
{
    /**
     * @var list<Store>
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

    public function persist(Store $store): void
    {
        $this->saved[] = $store;
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

final class SequentialUuidGenerator implements UuidGenerator
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
