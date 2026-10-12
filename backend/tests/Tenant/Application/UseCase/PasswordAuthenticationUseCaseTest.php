<?php

declare(strict_types=1);

namespace App\Tests\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\UserPasswordRepository;
use App\Tenant\Application\Dto\AuthenticatedUserView;
use App\Tenant\Application\Dto\PasswordLoginInput;
use App\Tenant\Application\Dto\ResetPasswordInput;
use App\Tenant\Application\Exception\InvalidPasswordInput;
use App\Tenant\Application\UseCase\LoginWithPasswordUseCase;
use App\Tenant\Application\UseCase\ResetPasswordUseCase;
use PHPUnit\Framework\TestCase;

final class PasswordAuthenticationUseCaseTest extends TestCase
{
    public function testLogsInWithNormalizedEmailAndPassword(): void
    {
        $repository = new PasswordUserRepository('owner@example.ru', 'Password2026');
        $result = (new LoginWithPasswordUseCase($repository))->execute(
            new PasswordLoginInput(' OWNER@EXAMPLE.RU ', 'Password2026'),
        );

        self::assertSame('owner@example.ru', $result->email);
        self::assertSame('+79123456789', $result->verifiedPhone);
    }

    public function testRejectsWrongPasswordWithGenericMessage(): void
    {
        $useCase = new LoginWithPasswordUseCase(new PasswordUserRepository('owner@example.ru', 'Password2026'));

        $this->expectException(InvalidPasswordInput::class);
        $this->expectExceptionMessage('Неверный email или пароль.');
        $useCase->execute(new PasswordLoginInput('owner@example.ru', 'wrong'));
    }

    public function testResetsPasswordAndFlushes(): void
    {
        $repository = new PasswordUserRepository('owner@example.ru', 'Password2026');
        $flusher = new PasswordFlusher();
        (new ResetPasswordUseCase($repository, $flusher))->execute(
            new ResetPasswordInput('owner@example.ru', 'NewPassword2027', 'NewPassword2027'),
        );

        self::assertSame('NewPassword2027', $repository->password);
        self::assertSame(1, $flusher->calls);
    }

    public function testRejectsWeakPassword(): void
    {
        $useCase = new ResetPasswordUseCase(
            new PasswordUserRepository('owner@example.ru', 'Password2026'),
            new PasswordFlusher(),
        );

        $this->expectException(InvalidPasswordInput::class);
        $this->expectExceptionMessage('от 10 до 128');
        $useCase->execute(new ResetPasswordInput('owner@example.ru', 'short1', 'short1'));
    }

    public function testRejectsMismatchedPasswords(): void
    {
        $useCase = new ResetPasswordUseCase(
            new PasswordUserRepository('owner@example.ru', 'Password2026'),
            new PasswordFlusher(),
        );

        $this->expectException(InvalidPasswordInput::class);
        $this->expectExceptionMessage('Пароли не совпадают.');
        $useCase->execute(new ResetPasswordInput('owner@example.ru', 'NewPassword2027', 'Different2027'));
    }
}

final class PasswordUserRepository implements UserPasswordRepository
{
    public function __construct(
        private readonly string $email,
        public string $password,
    ) {
    }

    public function authenticate(string $email, string $password): ?AuthenticatedUserView
    {
        return $email === $this->email && $password === $this->password
            ? new AuthenticatedUserView($email, '+79123456789')
            : null;
    }

    public function changePassword(string $email, string $password): bool
    {
        if ($email !== $this->email) {
            return false;
        }

        $this->password = $password;

        return true;
    }

    public function hasVerifiedPhone(string $email): bool
    {
        return $email === $this->email;
    }
}

final class PasswordFlusher implements EntityFlusher
{
    public int $calls = 0;

    public function flush(): void
    {
        ++$this->calls;
    }
}
