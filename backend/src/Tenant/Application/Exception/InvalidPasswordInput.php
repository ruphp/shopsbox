<?php

declare(strict_types=1);

namespace App\Tenant\Application\Exception;

use RuntimeException;

final class InvalidPasswordInput extends RuntimeException
{
    public function __construct(public readonly string $field, string $message)
    {
        parent::__construct($message);
    }
}
