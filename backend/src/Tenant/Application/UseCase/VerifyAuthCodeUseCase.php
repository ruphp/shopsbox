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
        $channel = $input->channel === 'phone' ? 'phone' : 'email';
        $email = strtolower(trim($input->email));
        $phone = preg_replace('/[\s()-]/', '', trim($input->phone)) ?? '';
        $code = trim($input->code);

        if ($channel === 'email' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidAuthCodeInput::forField('email', 'Email must be valid.');
        }

        if ($channel === 'phone' && !preg_match('/^\+79\d{9}$/', $phone)) {
            throw InvalidAuthCodeInput::forField('phone', 'Phone must use +79XXXXXXXXX format.');
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            throw InvalidAuthCodeInput::forField('code', 'Code must contain 6 digits.');
        }

        $recipient = $channel === 'phone' ? $phone : $email;
        $authCode = $this->authCodeRepository->findLatestOpenByRecipient($channel, $recipient);
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

        $authCode->consume($input->ip, $input->userAgent);
        $this->entityFlusher->flush();

        return new VerifyAuthCodeResult($authCode->email(), true, $channel, $recipient, (string) $authCode->phone());
    }
}
