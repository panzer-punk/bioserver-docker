<?php

declare(strict_types=1);

namespace App\Application\Actions\Login\ValueObjects;

use App\Domain\Login\PasswordValidatorInterface;
use InvalidArgumentException;

final class Password
{
    public readonly string $value;

    public function __construct(
        string $value,
        PasswordValidatorInterface $validator
    ) {
        if (! $validator->valid($value)) {
            throw new InvalidArgumentException("Password doesn't match criteria.");
        }

        $this->value = md5($value);
    }
}