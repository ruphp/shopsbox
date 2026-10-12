<?php

declare(strict_types=1);

namespace App\Tests\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\UserPhoneRepository;
use App\Tenant\Application\UseCase\ConfirmUserPhoneUseCase;
use PHPUnit\Framework\TestCase;

final class ConfirmUserPhoneUseCaseTest extends TestCase
{
    public function testItConfirmsPhoneForExistingUser(): void
    {
        $users = $this->createMock(UserPhoneRepository::class);
        $users->expects(self::once())
            ->method('confirmForEmail')
            ->with('owner@example.test', '+79990000000', '127.0.0.1', 'Browser', 'sitewidget_sms')
            ->willReturn(true);

        $flusher = $this->createMock(EntityFlusher::class);
        $flusher->expects(self::once())->method('flush');

        self::assertTrue((new ConfirmUserPhoneUseCase($users, $flusher))->execute(
            'owner@example.test',
            '+79990000000',
            '127.0.0.1',
            'Browser',
        ));
    }

    public function testItDoesNotFlushWhenUserCannotBeUpdated(): void
    {
        $users = $this->createStub(UserPhoneRepository::class);
        $users->method('confirmForEmail')->willReturn(false);
        $flusher = $this->createMock(EntityFlusher::class);
        $flusher->expects(self::never())->method('flush');

        self::assertFalse((new ConfirmUserPhoneUseCase($users, $flusher))->execute(
            'missing@example.test',
            '+79990000000',
            null,
            null,
        ));
    }
}
