<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Adapters;

use App\Tenant\Application\Contracts\AuthCodeDelivery;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class DevFileAuthCodeDelivery implements AuthCodeDelivery
{
    public function __construct(
        private string $projectDir,
        private RequestStack $requestStack,
    ) {
    }

    public function deliver(string $channel, string $recipient, string $code, DateTimeImmutable $expiresAt): void
    {
        $path = $this->projectDir . '/var/auth-codes.log';
        $line = sprintf(
            "[%s] channel=%s recipient=%s code=%s expires_at=%s provider=flash_dev\n",
            (new DateTimeImmutable())->format(DATE_ATOM),
            $channel,
            $recipient,
            $code,
            $expiresAt->format(DATE_ATOM),
        );

        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);

        $this->requestStack->getCurrentRequest()?->getSession()->getFlashBag()->add(
            'auth_code',
            sprintf('Dev-code for %s: %s', $recipient, $code),
        );
    }
}
