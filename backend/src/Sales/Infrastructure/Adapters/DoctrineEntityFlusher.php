<?php

declare(strict_types=1);

namespace App\Sales\Infrastructure\Adapters;

use App\Sales\Application\Contracts\EntityFlusher;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineEntityFlusher implements EntityFlusher
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
