<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\AuthCodeDelivery;
use App\Tenant\Application\Contracts\AuthCodeRepository;
use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\UuidGenerator;
use App\Tenant\Application\Dto\RequestAuthCodeInput;
use App\Tenant\Application\Dto\RequestAuthCodeResult;
use App\Tenant\Application\Exception\InvalidAuthCodeInput;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\AuthCode;
use DateTimeImmutable;

final readonly class RequestAuthCodeUseCase
{
    private const CODE_TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const MAX_REQUESTS_PER_HOUR = 3;

    public function __construct(
        private AuthCodeRepository $authCodeRepository,
        private AuthCodeDelivery $authCodeDelivery,
        private EntityFlusher $entityFlusher,
        private UuidGenerator $uuidGenerator,
    ) {
    }

    public function execute(RequestAuthCodeInput $input): RequestAuthCodeResult
    {
        $channel = $this->normalizeChannel($input->channel);
        $email = strtolower(trim($input->email));
        $phone = $this->normalizePhone($input->phone);

        if ($channel === 'email' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidAuthCodeInput::forField('email', 'Email must be valid.');
        }

        if ($channel === 'phone' && !preg_match('/^\+79\d{9}$/', $phone)) {
            throw InvalidAuthCodeInput::forField('phone', 'Phone must use +79XXXXXXXXX format.');
        }

        $recipient = $channel === 'phone' ? $phone : $email;
        if (!$input->allowNewRecipient && !$this->authCodeRepository->registeredRecipientExists($channel, $recipient)) {
            throw InvalidAuthCodeInput::forField($channel, 'Account was not found.');
        }

        $openCode = $this->authCodeRepository->findLatestOpenByRecipient($channel, $recipient);
        if ($openCode instanceof AuthCode && !$openCode->isExpired() && $openCode->hasAttemptsLeft()) {
            throw InvalidAuthCodeInput::forField($channel, 'Confirm the active code before requesting another one.');
        }

        if ($this->authCodeRepository->countRecentRequestsByRecipient(
            $channel,
            $recipient,
            new DateTimeImmutable('-1 hour'),
        ) >= self::MAX_REQUESTS_PER_HOUR) {
            throw InvalidAuthCodeInput::forField($channel, 'Too many code requests. Try again later.');
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = new DateTimeImmutable(sprintf('+%d minutes', self::CODE_TTL_MINUTES));

        $this->authCodeRepository->persist(new AuthCode(
            $this->uuidGenerator->generate(),
            $email,
            hash('sha256', $code),
            $expiresAt,
            self::MAX_ATTEMPTS,
            $channel === 'phone' ? $phone : null,
            $channel,
            'flash_dev',
        ));
        $this->authCodeDelivery->deliver($channel, $recipient, $code, $expiresAt);
        $this->entityFlusher->flush();

        return new RequestAuthCodeResult($email, $expiresAt, $channel, $recipient, $phone);
    }

    private function normalizeChannel(string $channel): string
    {
        return $channel === 'phone' ? 'phone' : 'email';
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[\s()-]/', '', trim($phone)) ?? '';
    }
}
