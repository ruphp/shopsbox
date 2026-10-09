<?php

declare(strict_types=1);

namespace App\Tests\Tenant\Infrastructure\Adapters;

use App\Tenant\Infrastructure\Adapters\SiteWidgetSmsAuthCodeDelivery;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class SiteWidgetSmsAuthCodeDeliveryTest extends TestCase
{
    public function testItCreatesAnAuthenticatedSmsTask(): void
    {
        $expiresAt = new DateTimeImmutable('2026-10-09T18:00:00+05:00');
        $client = new MockHttpClient(function (string $method, string $url, array $options) use ($expiresAt): MockResponse {
            self::assertSame('POST', $method);
            self::assertSame('https://sitewidget.ru/api/sms/v1/tasks', $url);
            self::assertContains('Authorization: Bearer server-only-token', $options['normalized_headers']['authorization']);
            self::assertSame([
                'requestId' => 'request-id',
                'phone' => '+79990000000',
                'text' => 'Код подтверждения ShopsBox: 123456',
                'expiresAt' => $expiresAt->format(DATE_ATOM),
            ], json_decode($options['body'], true, 512, JSON_THROW_ON_ERROR));

            return new MockResponse('', ['http_code' => 202]);
        });

        $delivery = new SiteWidgetSmsAuthCodeDelivery(
            $client,
            'https://sitewidget.ru/api/sms/v1/tasks',
            'server-only-token',
        );

        $delivery->deliver('request-id', 'phone', '+79990000000', '123456', $expiresAt);
    }
}
