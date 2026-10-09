<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Adapters;

use App\Tenant\Application\Contracts\AuthCodeDelivery;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class SiteWidgetSmsAuthCodeDelivery implements AuthCodeDelivery
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiUrl,
        private string $token,
    ) {
    }

    public function deliver(
        string $requestId,
        string $channel,
        string $recipient,
        string $code,
        DateTimeImmutable $expiresAt,
    ): void {
        if ($channel !== 'phone') {
            throw new RuntimeException('SiteWidget SMS delivery only supports phone codes.');
        }
        if ($this->token === '') {
            throw new RuntimeException('SITEWIDGET_SMS_TOKEN is not configured.');
        }

        $response = $this->httpClient->request('POST', $this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ],
            'json' => [
                'requestId' => $requestId,
                'phone' => $recipient,
                'text' => 'Код подтверждения ShopsBox: ' . $code,
                'expiresAt' => $expiresAt->format(DATE_ATOM),
            ],
            'timeout' => 5,
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException(sprintf('SiteWidget rejected SMS task with HTTP %d.', $statusCode));
        }
    }
}
