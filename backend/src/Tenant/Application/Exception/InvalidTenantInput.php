<?php

declare(strict_types=1);

namespace App\Tenant\Application\Exception;

use RuntimeException;

final class InvalidTenantInput extends RuntimeException
{
    private function __construct(public readonly string $field, string $message)
    {
        parent::__construct($message);
    }

    public static function forField(string $field, string $message): self
    {
        return new self($field, $message);
    }
}
