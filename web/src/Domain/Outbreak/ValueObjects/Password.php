<?php

declare(strict_types=1);

namespace App\Domain\Outbreak\ValueObjects;

use App\Domain\Outbreak\PasswordValidatorInterface;
use InvalidArgumentException;

final class Password
{
    public readonly string $value;

    public function __construct(
        string $value,
        PasswordValidatorInterface $validator
    ) {
        if (! $validator->valid($value)) {
            throw new InvalidArgumentException("Password does not match criteria.");
        }

        $this->value = md5($value);
    }
}
