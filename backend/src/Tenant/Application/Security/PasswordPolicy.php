<?php

declare(strict_types=1);

namespace App\Tenant\Application\Security;

final class PasswordPolicy
{
    public static function validationError(string $password, string $confirmation): ?string
    {
        if ($password !== $confirmation) {
            return 'Пароли не совпадают.';
        }
        if (mb_strlen($password) < 10 || mb_strlen($password) > 128) {
            return 'Пароль должен содержать от 10 до 128 символов.';
        }
        if (preg_match('/\p{L}/u', $password) !== 1 || preg_match('/\d/', $password) !== 1) {
            return 'Пароль должен содержать хотя бы одну букву и одну цифру.';
        }

        return null;
    }
}
