<?php

declare(strict_types=1);

namespace App\Application\Actions\Outbreak\Validators\Password;

use App\Domain\Outbreak\PasswordValidatorInterface;

final class DevPasswordValidator implements PasswordValidatorInterface
{
    public function valid(string $password): bool
    {
        return preg_match("/^.+$/", $password) === 1;
    }

    public function criteria(): string
    {
        return "Must be at least 1 character long.";
    }
}
