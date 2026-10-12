<?php

declare(strict_types=1);

namespace App\Sales\Application\Contracts;

interface EntityFlusher
{
    public function flush(): void;
}
