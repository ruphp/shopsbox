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

    public function __construct(
        private AuthCodeRepository $authCodeRepository,
        private AuthCodeDelivery $authCodeDelivery,
        private EntityFlusher $entityFlusher,
        private UuidGenerator $uuidGenerator,
    ) {
    }

    public function execute(RequestAuthCodeInput $input): RequestAuthCodeResult
    {
        $email = strtolower(trim($input->email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidAuthCodeInput::forField('email', 'Email must be valid.');
        }

        $code = (string) random_int(100000, 999999);
        $expiresAt = new DateTimeImmutable(sprintf('+%d minutes', self::CODE_TTL_MINUTES));

        $this->authCodeRepository->persist(new AuthCode(
            $this->uuidGenerator->generate(),
            $email,
            hash('sha256', $code),
            $expiresAt,
            self::MAX_ATTEMPTS,
        ));
        $this->authCodeDelivery->deliver($email, $code, $expiresAt);
        $this->entityFlusher->flush();

        return new RequestAuthCodeResult($email, $expiresAt);
    }
}
