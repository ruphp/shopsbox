<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\AuthCodeRepository;
use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Dto\VerifyAuthCodeInput;
use App\Tenant\Application\Dto\VerifyAuthCodeResult;
use App\Tenant\Application\Exception\InvalidAuthCodeInput;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\AuthCode;

final readonly class VerifyAuthCodeUseCase
{
    public function __construct(
        private AuthCodeRepository $authCodeRepository,
        private EntityFlusher $entityFlusher,
    ) {
    }

    public function execute(VerifyAuthCodeInput $input): VerifyAuthCodeResult
    {
        $email = strtolower(trim($input->email));
        $code = trim($input->code);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidAuthCodeInput::forField('email', 'Email must be valid.');
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            throw InvalidAuthCodeInput::forField('code', 'Code must contain 6 digits.');
        }

        $authCode = $this->authCodeRepository->findLatestOpenByEmail($email);
        if (!$authCode instanceof AuthCode) {
            throw InvalidAuthCodeInput::forField('code', 'Code is expired or not found.');
        }

        if ($authCode->isExpired()) {
            throw InvalidAuthCodeInput::forField('code', 'Code is expired or not found.');
        }

        if (!$authCode->hasAttemptsLeft()) {
            throw InvalidAuthCodeInput::forField('code', 'Too many attempts.');
        }

        if (!$authCode->matches($code)) {
            $authCode->recordFailedAttempt();
            $this->entityFlusher->flush();

            throw InvalidAuthCodeInput::forField('code', 'Code is invalid.');
        }

        $authCode->consume();
        $this->entityFlusher->flush();

        return new VerifyAuthCodeResult($email, true);
    }
}
