<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\AuthCode;
use DateTimeImmutable;

interface AuthCodeRepository
{
    public function persist(AuthCode $authCode): void;

    public function findLatestOpenByEmail(string $email): ?AuthCode;

    public function findLatestOpenByRecipient(string $channel, string $recipient): ?AuthCode;

    public function registeredRecipientExists(string $channel, string $recipient): bool;

    public function countRecentRequestsByRecipient(string $channel, string $recipient, DateTimeImmutable $since): int;
}
