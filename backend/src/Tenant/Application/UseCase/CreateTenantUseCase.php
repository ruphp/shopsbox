<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Application\Contracts\TenantRepository;
use App\Tenant\Application\Contracts\UuidGenerator;
use App\Tenant\Application\Dto\CreateTenantInput;
use App\Tenant\Application\Dto\CreateTenantResult;
use App\Tenant\Application\Exception\InvalidTenantInput;
use DateTimeZone;

final class CreateTenantUseCase
{
    public function __construct(
        private readonly TenantRepository $tenantRepository,
        private readonly StoreRepository $storeRepository,
        private readonly EntityFlusher $entityFlusher,
        private readonly UuidGenerator $uuidGenerator,
    ) {
    }

    public function execute(CreateTenantInput $input): CreateTenantResult
    {
        $this->validateInput($input);
        $this->guardStoreDomainIsFree($input->storeDomain);

        $tenantId = $this->uuidGenerator->generate();
        $storeId = $this->uuidGenerator->generate();

        $this->tenantRepository->persist(
            $tenantId,
            $input->tenantName,
            'active',
            $input->billingEmail,
        );

        $this->storeRepository->persist(
            $storeId,
            $tenantId,
            $input->storeName,
            $input->storeSlug,
            $input->storeDomain,
            'active',
            $input->defaultCurrency,
            $input->timezone,
        );
        $this->entityFlusher->flush();

        return new CreateTenantResult($tenantId, $storeId);
    }

    private function validateInput(CreateTenantInput $input): void
    {
        if (!filter_var($input->billingEmail, FILTER_VALIDATE_EMAIL)) {
            throw InvalidTenantInput::forField('billing_email', 'Invalid email format.');
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $input->storeSlug)) {
            throw InvalidTenantInput::forField('store_slug', 'Slug must contain lowercase letters, digits and hyphens.');
        }

        if (!preg_match('/^[a-z0-9.-]+$/', $input->storeDomain)) {
            throw InvalidTenantInput::forField('store_domain', 'Domain must contain lowercase letters, digits, dots and hyphens.');
        }

        if (!preg_match('/^[A-Z]{3}$/', $input->defaultCurrency)) {
            throw InvalidTenantInput::forField('default_currency', 'Currency must be a 3-letter ISO code.');
        }

        if (!in_array($input->timezone, DateTimeZone::listIdentifiers(), true)) {
            throw InvalidTenantInput::forField('timezone', 'Timezone must be a valid IANA identifier.');
        }
    }

    private function guardStoreDomainIsFree(string $domain): void
    {
        if ($this->storeRepository->existsByDomain($domain)) {
            throw InvalidTenantInput::forField('store_domain', 'Store domain is already used.');
        }
    }
}
