<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Adapters;

use App\Tenant\Application\Contracts\EntityFlusher;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineEntityFlusher implements EntityFlusher
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
