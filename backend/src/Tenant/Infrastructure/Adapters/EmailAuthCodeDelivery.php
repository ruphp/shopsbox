<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Adapters;

use App\Tenant\Application\Contracts\AuthCodeDelivery;
use DateTimeImmutable;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class EmailAuthCodeDelivery implements AuthCodeDelivery
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromAddress,
    ) {
    }

    public function deliver(
        string $requestId,
        string $channel,
        string $recipient,
        string $code,
        DateTimeImmutable $expiresAt,
    ): void {
        $this->mailer->send(
            (new Email())
                ->from($this->fromAddress)
                ->to($recipient)
                ->subject('Код восстановления пароля ShopsBox')
                ->text(sprintf(
                    "Код для восстановления пароля: %s\n\nКод действует до %s. Если вы не запрашивали восстановление, проигнорируйте это письмо.\n",
                    $code,
                    $expiresAt->format('H:i'),
                )),
        );
    }
}
