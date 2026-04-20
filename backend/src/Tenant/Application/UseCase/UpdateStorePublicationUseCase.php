<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Application\Dto\StoreSettingsView;
use App\Tenant\Application\Dto\UpdateStorePublicationInput;
use App\Tenant\Application\Exception\InvalidTenantInput;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;

final readonly class UpdateStorePublicationUseCase
{
    private const RESERVED_SUBDOMAINS = [
        'www', 'api', 'admin', 'app', 'static', 'assets', 'cdn', 'mail', 'smtp', 'ftp',
        'support', 'help', 'billing', 'status', 'demo', 'shopsbox',
    ];

    public function __construct(
        private StoreRepository $storeRepository,
        private EntityFlusher $entityFlusher,
    ) {
    }

    public function execute(UpdateStorePublicationInput $input): StoreSettingsView
    {
        $current = $this->storeRepository->findSettingsByOwnerEmail(strtolower(trim($input->ownerEmail)));
        if (!$current instanceof StoreSettingsView) {
            throw new StoreSettingsAccessDenied('Store settings are not available for this user.');
        }

        $ownerName = trim($input->ownerName);
        $email = strtolower(trim($input->email));
        $phone = trim($input->phone);
        $subdomain = strtolower(trim($input->publicSubdomain));

        if ($ownerName === '' || strlen($ownerName) > 120) {
            throw InvalidTenantInput::forField('owner_name', 'Owner name must be from 1 to 120 characters.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidTenantInput::forField('publication_email', 'Publication email must be valid.');
        }

        if ($phone === '' || strlen($phone) > 40) {
            throw InvalidTenantInput::forField('publication_phone', 'Publication phone is required.');
        }

        if (!$this->validSubdomain($subdomain)) {
            throw InvalidTenantInput::forField('public_subdomain', 'Subdomain must contain 3-40 latin letters, digits or hyphen.');
        }

        if (in_array($subdomain, self::RESERVED_SUBDOMAINS, true)) {
            throw InvalidTenantInput::forField('public_subdomain', 'This subdomain is reserved.');
        }

        if ($this->storeRepository->existsByPublicSubdomain($subdomain, $current->storeId)) {
            throw InvalidTenantInput::forField('public_subdomain', 'This subdomain is already used.');
        }

        if (!$input->termsAccepted) {
            throw InvalidTenantInput::forField('terms', 'Publication responsibility must be accepted.');
        }

        $updated = $this->storeRepository->updatePublicationForOwnerEmail(
            strtolower(trim($input->ownerEmail)),
            $ownerName,
            $email,
            $phone,
            $subdomain,
            true,
        );

        if (!$updated instanceof StoreSettingsView) {
            throw new StoreSettingsAccessDenied('Store settings are not available for this user.');
        }

        $this->entityFlusher->flush();

        return $updated;
    }

    private function validSubdomain(string $value): bool
    {
        return preg_match('/^[a-z0-9](?:[a-z0-9-]{1,38}[a-z0-9])$/', $value) === 1;
    }
}
