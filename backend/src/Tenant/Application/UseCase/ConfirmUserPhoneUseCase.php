<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\UserPhoneRepository;

final readonly class ConfirmUserPhoneUseCase
{
    public function __construct(
        private UserPhoneRepository $users,
        private EntityFlusher $entityFlusher,
    ) {
    }

    public function execute(
        string $email,
        string $phone,
        ?string $ip,
        ?string $userAgent,
        string $provider = 'sitewidget_sms',
    ): bool {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/^\+79\d{9}$/', $phone) !== 1) {
            return false;
        }

        if (!$this->users->confirmForEmail($email, $phone, $ip, $userAgent, $provider)) {
            return false;
        }

        $this->entityFlusher->flush();

        return true;
    }
}
