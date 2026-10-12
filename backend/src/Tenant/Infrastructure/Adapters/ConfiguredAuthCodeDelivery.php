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
        private EmailAuthCodeDelivery $emailDelivery,
        private string $smsProvider,
        private string $emailProvider,
    ) {
    }

    public function deliver(
        string $requestId,
        string $channel,
        string $recipient,
        string $code,
        DateTimeImmutable $expiresAt,
    ): void {
        if ($channel === 'email') {
            if ($this->emailProvider === 'dev_flash') {
                $this->devDelivery->deliver($requestId, $channel, $recipient, $code, $expiresAt);

                return;
            }
            if ($this->emailProvider === 'mailer') {
                $this->emailDelivery->deliver($requestId, $channel, $recipient, $code, $expiresAt);

                return;
            }

            throw new RuntimeException(sprintf('Unsupported AUTH_EMAIL_PROVIDER "%s".', $this->emailProvider));
        }

        if ($this->smsProvider === 'dev_flash') {
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
