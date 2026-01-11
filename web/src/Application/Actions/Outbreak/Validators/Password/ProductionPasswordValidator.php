<?php

declare(strict_types=1);

namespace App\Application\Actions\Outbreak\Validators\Password;

use App\Domain\Outbreak\PasswordValidatorInterface;

final class ProductionPasswordValidator implements PasswordValidatorInterface
{
    public function valid(string $password): bool
    {
        $criterias = 0;
        $len       = mb_strlen($password);

        if ($len < 6) {
            return false;
        }

        if (preg_match("/.*\\d.*/", $password) === 1) {
            $criterias++;
        }

        if (preg_match("/.*[A-Z].*/", $password) === 1) {
            $criterias++;
        }

        if (preg_match("/.*[a-z].*/", $password) === 1) {
            $criterias++;
        }

        return $criterias === 3;
    }

    public function criteria(): string
    {
        return "Must be at least 6 characters long, must contain letters in mixed case and must contain numbers.";
    }
}
