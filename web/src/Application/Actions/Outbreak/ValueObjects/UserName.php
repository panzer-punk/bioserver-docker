<?php

declare(strict_types=1);

namespace App\Application\Actions\Outbreak\ValueObjects;

use App\Domain\Outbreak\UserNameValidatorInterface;
use InvalidArgumentException;

final class UserName
{
    public readonly string $value;

    public function __construct(
        string $value,
        UserNameValidatorInterface $validator
    ) {
        if (! $validator->valid($value)) {
            throw new InvalidArgumentException("Username does not match criteria.");
        }

        $this->value = mb_strtolower($value);
    }
}
