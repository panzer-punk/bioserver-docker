<?php

declare(strict_types=1);

namespace App\Application\Actions\Outbreak\Validators\UserName;

use App\Domain\Outbreak\UserNameValidatorInterface;

final class ProductionUserNameValidator implements UserNameValidatorInterface
{
    public function valid(string $username): bool
    {
        return preg_match("/^\\w{3,14}/", $username) === 1;
    }

    public function criteria(): string
    {
        return "Username must be between 3 characters and 14 characters long and use only ASCII characters, so no special symbols.";
    }
}

