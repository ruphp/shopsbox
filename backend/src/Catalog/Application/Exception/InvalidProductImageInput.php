<?php

declare(strict_types=1);

namespace App\Catalog\Application\Exception;

use RuntimeException;

final class InvalidProductImageInput extends RuntimeException
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
