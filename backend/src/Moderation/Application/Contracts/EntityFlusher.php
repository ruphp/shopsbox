<?php

declare(strict_types=1);

namespace App\Moderation\Application\Contracts;

interface EntityFlusher
{
    public function flush(): void;
}
