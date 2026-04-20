<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\OwnerRegistrationRepository;
use App\Tenant\Application\Contracts\UuidGenerator;
use App\Tenant\Application\Dto\RegisterOwnerInput;
use App\Tenant\Application\Dto\RegisterOwnerResult;
use App\Tenant\Application\Exception\InvalidTenantInput;
use DateTimeZone;

final readonly class RegisterOwnerUseCase
{
    private const RESERVED_STORE_SLUGS = [
        'demo',
        'www',
        'api',
        'admin',
        'app',
        'static',
        'assets',
        'cdn',
        'mail',
        'support',
        'help',
        'billing',
        'status',
        'shopsbox',
    ];

    public function __construct(
        private OwnerRegistrationRepository $registrationRepository,
        private EntityFlusher $entityFlusher,
        private UuidGenerator $uuidGenerator,
    ) {
    }

    public function execute(RegisterOwnerInput $input): RegisterOwnerResult
    {
        $ownerName = trim($input->ownerName);
        $email = strtolower(trim($input->email));
        $phone = trim($input->phone);
        $storeName = trim($input->storeName);
        $storeSlug = strtolower(trim($input->storeSlug));
        $storeDomain = $storeSlug . '.shopsbox.ru';
        $timezone = $this->normalizeTimezone(trim($input->timezone));

        if ($ownerName === '' || mb_strlen($ownerName) > 120) {
            throw InvalidTenantInput::forField('owner_name', 'Owner name must be from 1 to 120 characters.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidTenantInput::forField('email', 'Email must be valid.');
        }
        if (!preg_match('/^\+79\d{9}$/', $phone)) {
            throw InvalidTenantInput::forField('phone', 'Phone must use +79XXXXXXXXX format.');
        }
        if ($storeName === '' || mb_strlen($storeName) > 160) {
            throw InvalidTenantInput::forField('store_name', 'Store name must be from 1 to 160 characters.');
        }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $storeSlug)) {
            throw InvalidTenantInput::forField('store_slug', 'Store slug must contain lowercase letters, digits and hyphens.');
        }
        if (in_array($storeSlug, self::RESERVED_STORE_SLUGS, true)) {
            throw InvalidTenantInput::forField('store_slug', 'Store subdomain is reserved.');
        }
        if ($timezone === null || !in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            throw InvalidTenantInput::forField('timezone', 'Timezone must be a valid IANA identifier.');
        }
        if ($this->registrationRepository->emailExists($email)) {
            throw InvalidTenantInput::forField('email', 'Email is already registered.');
        }
        if ($this->registrationRepository->storeSlugExists($storeSlug)) {
            throw InvalidTenantInput::forField('store_slug', 'Store slug is already used.');
        }
        if ($this->registrationRepository->storeDomainExists($storeDomain)) {
            throw InvalidTenantInput::forField('store_slug', 'Store subdomain is already used.');
        }

        $result = $this->registrationRepository->register(
            $this->uuidGenerator->generate(),
            $this->uuidGenerator->generate(),
            $this->uuidGenerator->generate(),
            $ownerName,
            $email,
            $phone,
            $storeName,
            $storeSlug,
            $storeDomain,
            $timezone,
        );
        $this->entityFlusher->flush();

        return $result;
    }

    private function normalizeTimezone(string $timezone): ?string
    {
        return match ($timezone) {
            '+02:00' => 'Europe/Kaliningrad',
            '+03:00' => 'Europe/Moscow',
            '+04:00' => 'Europe/Samara',
            '+05:00' => 'Asia/Yekaterinburg',
            '+06:00' => 'Asia/Omsk',
            '+07:00' => 'Asia/Krasnoyarsk',
            '+08:00' => 'Asia/Irkutsk',
            '+09:00' => 'Asia/Yakutsk',
            '+10:00' => 'Asia/Vladivostok',
            '+11:00' => 'Asia/Magadan',
            '+12:00' => 'Asia/Kamchatka',
            default => $timezone,
        };
    }
}
