<?php

declare(strict_types=1);

namespace App\Moderation\Infrastructure\Adapters;

use App\Moderation\Application\Contracts\EntityFlusher;
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
