<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Adapters;

use App\Tenant\Application\Contracts\AuthCodeDelivery;
use DateTimeImmutable;
use RuntimeException;

final readonly class ConfiguredAuthCodeDelivery implements AuthCodeDelivery
{
    public function __construct(
        private DevFileAuthCodeDelivery $devDelivery,
        private SiteWidgetSmsAuthCodeDelivery $siteWidgetDelivery,
        private string $smsProvider,
    ) {
    }

    public function deliver(
        string $requestId,
        string $channel,
        string $recipient,
        string $code,
        DateTimeImmutable $expiresAt,
    ): void {
        if ($channel !== 'phone' || $this->smsProvider === 'dev_flash') {
            $this->devDelivery->deliver($requestId, $channel, $recipient, $code, $expiresAt);

            return;
        }

        if ($this->smsProvider === 'sitewidget') {
            $this->siteWidgetDelivery->deliver($requestId, $channel, $recipient, $code, $expiresAt);

            return;
        }

        throw new RuntimeException(sprintf('Unsupported SMS_PROVIDER "%s".', $this->smsProvider));
    }
}
