<?php

declare(strict_types=1);

namespace App\Application\Actions\Login\ValueObjects;

use App\Domain\Login\UserNameValidatorInterface;
use InvalidArgumentException;

final class UserName
{
    public readonly string $value;

    public function __construct(
        string $value,
        UserNameValidatorInterface $validator
    ) {
        if (! $validator->valid($value)) {
            throw new InvalidArgumentException("Username doesn't match criteria.");
        }

        $this->value = mb_strtolower($value);
    }
}
