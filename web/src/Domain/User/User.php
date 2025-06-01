<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable
{
    public function __construct(
        public readonly string $username
    ) {

    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'username' => $this->username,
        ];
    }
}
