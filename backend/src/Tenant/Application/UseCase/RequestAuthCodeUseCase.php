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
    private const CODE_TTL_MINUTES = 5;
    private const MAX_ATTEMPTS = 5;
    private const MAX_REQUESTS_PER_HOUR = 3;
    private const MAX_REQUESTS_PER_IP_PER_HOUR = 10;

    public function __construct(
        private AuthCodeRepository $authCodeRepository,
        private AuthCodeDelivery $authCodeDelivery,
        private EntityFlusher $entityFlusher,
        private UuidGenerator $uuidGenerator,
        private string $authCodeSecret,
        private string $smsProvider,
        private string $emailProvider,
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
            throw InvalidAuthCodeInput::forField('phone', 'Укажите мобильный номер РФ в формате +79XXXXXXXXX.');
        }

        $recipient = $channel === 'phone' ? $phone : $email;
        if (!$input->allowNewRecipient && !$this->authCodeRepository->registeredRecipientExists($channel, $recipient)) {
            throw InvalidAuthCodeInput::forField($channel, 'Account was not found.');
        }
        if ($input->allowNewRecipient && $this->authCodeRepository->registeredRecipientExists($channel, $recipient)) {
            throw InvalidAuthCodeInput::forField($channel, 'Этот номер уже зарегистрирован. Войдите по email или восстановите доступ.');
        }

        $openCode = $this->authCodeRepository->findLatestOpenByRecipient($channel, $recipient);
        if ($openCode instanceof AuthCode && !$openCode->isExpired() && $openCode->hasAttemptsLeft()) {
            throw InvalidAuthCodeInput::forField($channel, 'Код уже отправлен. Введите его или дождитесь окончания срока действия.');
        }

        if ($this->authCodeRepository->countRecentRequestsByRecipient(
            $channel,
            $recipient,
            new DateTimeImmutable('-1 hour'),
        ) >= self::MAX_REQUESTS_PER_HOUR) {
            throw InvalidAuthCodeInput::forField($channel, 'Для этого номера превышен лимит запросов. Попробуйте позже.');
        }

        if ($input->requestIp !== '' && $this->authCodeRepository->countRecentRequestsByIp(
            $input->requestIp,
            new DateTimeImmutable('-1 hour'),
        ) >= self::MAX_REQUESTS_PER_IP_PER_HOUR) {
            throw InvalidAuthCodeInput::forField($channel, 'Слишком много запросов. Попробуйте позже.');
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = new DateTimeImmutable(sprintf('+%d minutes', self::CODE_TTL_MINUTES));

        $requestId = $this->uuidGenerator->generate();
        $this->authCodeRepository->persist(new AuthCode(
            $requestId,
            $email,
            hash_hmac('sha256', $code, $this->authCodeSecret),
            $expiresAt,
            self::MAX_ATTEMPTS,
            $channel === 'phone' ? $phone : null,
            $channel,
            $channel === 'phone' ? $this->smsProvider : $this->emailProvider,
            $input->requestIp !== '' ? $input->requestIp : null,
        ));
        $this->authCodeDelivery->deliver($requestId, $channel, $recipient, $code, $expiresAt);
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
