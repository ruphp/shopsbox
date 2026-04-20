<?php

declare(strict_types=1);

namespace App\Moderation\Application\UseCase;

use App\Moderation\Application\Contracts\EntityFlusher;
use App\Moderation\Application\Contracts\ModeratorRepository;
use RuntimeException;

final readonly class ManageModeratorsUseCase
{
    public function __construct(
        private ModeratorRepository $moderators,
        private EntityFlusher $flusher,
    ) {
    }

    public function list(): array
    {
        return $this->moderators->listModerators();
    }

    public function assign(string $email): void
    {
        if (!$this->moderators->assignByEmail(strtolower(trim($email)))) {
            throw new RuntimeException('User not found.');
        }

        $this->flusher->flush();
    }

    public function remove(string $email): void
    {
        if (!$this->moderators->removeByEmail(strtolower(trim($email)))) {
            throw new RuntimeException('Moderator role not found.');
        }

        $this->flusher->flush();
    }
}
