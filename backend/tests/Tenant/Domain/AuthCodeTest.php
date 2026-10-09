<?php

declare(strict_types=1);

namespace App\Tests\Tenant\Domain;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\AuthCode;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AuthCodeTest extends TestCase
{
    public function testItMatchesOnlyAnHmacCreatedWithTheApplicationSecret(): void
    {
        $secret = 'test-secret';
        $code = '123456';
        $authCode = new AuthCode(
            '019db8be-11fd-73c2-831d-76e628e24ad2',
            '',
            hash_hmac('sha256', $code, $secret),
            new DateTimeImmutable('+5 minutes'),
            5,
            '+79990000000',
            'phone',
            'sitewidget',
        );

        self::assertTrue($authCode->matches($code, $secret));
        self::assertFalse($authCode->matches('654321', $secret));
        self::assertFalse($authCode->matches($code, 'another-secret'));
    }

    public function testItStopsAcceptingAttemptsAfterFiveFailures(): void
    {
        $authCode = new AuthCode(
            '019db8be-11fd-73c2-831d-76e628e24ad2',
            '',
            hash_hmac('sha256', '123456', 'test-secret'),
            new DateTimeImmutable('+5 minutes'),
            5,
            '+79990000000',
            'phone',
            'sitewidget',
        );

        for ($attempt = 0; $attempt < 5; ++$attempt) {
            self::assertTrue($authCode->hasAttemptsLeft());
            $authCode->recordFailedAttempt();
        }

        self::assertFalse($authCode->hasAttemptsLeft());
    }
}
