<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Application\Dto\StoreSettingsView;
use App\Tenant\Application\Dto\UpdateStoreSettingsInput;
use App\Tenant\Application\Exception\InvalidTenantInput;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;

final readonly class UpdateStoreSettingsUseCase
{
    public function __construct(
        private StoreRepository $storeRepository,
        private EntityFlusher $entityFlusher,
    ) {
    }

    public function execute(UpdateStoreSettingsInput $input): StoreSettingsView
    {
        $name = trim($input->name);
        $description = $this->emptyToNull($input->publicDescription);
        $contactEmail = $this->emptyToNull($input->contactEmail);
        $contactPhone = $this->emptyToNull($input->contactPhone);
        $currency = strtoupper(trim($input->defaultCurrency));
        $timezone = trim($input->timezone);

        if ($name === '' || strlen($name) > 160) {
            throw InvalidTenantInput::forField('name', 'Store name must be from 1 to 160 characters.');
        }

        if ($description !== null && strlen($description) > 1000) {
            throw InvalidTenantInput::forField('public_description', 'Public description must be up to 1000 characters.');
        }

        if ($contactEmail !== null && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            throw InvalidTenantInput::forField('contact_email', 'Contact email must be valid.');
        }

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw InvalidTenantInput::forField('default_currency', 'Default currency must be ISO 4217 code.');
        }

        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            throw InvalidTenantInput::forField('timezone', 'Timezone must be valid.');
        }

        $view = $this->storeRepository->updateSettingsForOwnerEmail(
            strtolower(trim($input->ownerEmail)),
            $name,
            $description,
            $contactEmail,
            $contactPhone,
            $currency,
            $timezone,
        );

        if (!$view instanceof StoreSettingsView) {
            throw new StoreSettingsAccessDenied('Store settings are not available for this user.');
        }

        $this->entityFlusher->flush();

        return $view;
    }

    private function emptyToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
