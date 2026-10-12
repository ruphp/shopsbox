<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\UserPasswordRepository;
use App\Tenant\Application\Dto\ResetPasswordInput;
use App\Tenant\Application\Exception\InvalidPasswordInput;
use App\Tenant\Application\Security\PasswordPolicy;

final readonly class ResetPasswordUseCase
{
    public function __construct(
        private UserPasswordRepository $users,
        private EntityFlusher $flusher,
    ) {
    }

    public function execute(ResetPasswordInput $input): void
    {
        $error = PasswordPolicy::validationError($input->password, $input->passwordConfirmation);
        if ($error !== null) {
            throw new InvalidPasswordInput('password', $error);
        }
        if (!$this->users->changePassword(strtolower(trim($input->email)), $input->password)) {
            throw new InvalidPasswordInput('email', 'Аккаунт не найден.');
        }

        $this->flusher->flush();
    }
}
