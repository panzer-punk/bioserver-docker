<?php

declare(strict_types=1);

namespace App\Domain\Login;

interface UserNameValidatorInterface
{
    public function valid(string $username): bool;
    public function criteria(): string;
}
