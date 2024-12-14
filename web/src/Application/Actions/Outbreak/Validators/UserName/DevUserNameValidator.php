<?php

declare(strict_types=1);

namespace App\Application\Actions\Outbreak\Validators\UserName;

use App\Domain\Outbreak\UserNameValidatorInterface;

final class DevUserNameValidator implements UserNameValidatorInterface
{
    public function valid(string $username): bool
    {
        return preg_match("/^\\w+$/", $username) === 1;
    }

    public function criteria(): string
    {
        return "At least 1 character. No special symbols.";
    }
}
