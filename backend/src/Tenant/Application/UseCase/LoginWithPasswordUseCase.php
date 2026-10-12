<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\UserPasswordRepository;
use App\Tenant\Application\Dto\AuthenticatedUserView;
use App\Tenant\Application\Dto\PasswordLoginInput;
use App\Tenant\Application\Exception\InvalidPasswordInput;

final readonly class LoginWithPasswordUseCase
{
    public function __construct(private UserPasswordRepository $users)
    {
    }

    public function execute(PasswordLoginInput $input): AuthenticatedUserView
    {
        $email = strtolower(trim($input->email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $input->password === '') {
            throw new InvalidPasswordInput('credentials', 'Неверный email или пароль.');
        }

        $user = $this->users->authenticate($email, $input->password);
        if (!$user instanceof AuthenticatedUserView) {
            throw new InvalidPasswordInput('credentials', 'Неверный email или пароль.');
        }

        return $user;
    }
}
