<?php

declare(strict_types=1);

namespace App\Moderation\Application\Contracts;

use App\Moderation\Application\Dto\ModeratorView;

interface ModeratorRepository
{
    /**
     * @return list<ModeratorView>
     */
    public function listModerators(): array;

    public function assignByEmail(string $email): bool;

    public function removeByEmail(string $email): bool;
}
