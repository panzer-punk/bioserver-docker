<?php

declare(strict_types=1);

namespace App\Domain\Login;

interface PasswordValidatorInterface
{
    public function valid(string $password): bool;
    public function criteria(): string;
}
