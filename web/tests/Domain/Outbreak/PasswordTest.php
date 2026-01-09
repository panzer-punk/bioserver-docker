<?php

declare(strict_types=1);

namespace Tests\Domain\Outbreak;

use App\Domain\Outbreak\PasswordValidatorInterface;
use App\Domain\Outbreak\ValueObjects\Password;
use InvalidArgumentException;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    public function testPassword(): void
    {
        $passwordValue = "A375";
        $validator = new class implements PasswordValidatorInterface {
            public function valid(string $password): bool
            {
                return true;
            }

            public function criteria(): string
            {
                return "Dummy criteria.";
            }
        };

        $password = new Password($passwordValue, $validator);

        $this->assertEquals(md5($passwordValue), $password->value);
    }

    public function testPasswordFailure(): void
    {
        $passwordValue = "A375";
        $validator = new class implements PasswordValidatorInterface {
            public function valid(string $password): bool
            {
                return false;
            }

            public function criteria(): string
            {
                return "Dummy criteria.";
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Password does not match criteria.");

        new Password($passwordValue, $validator);
    }
}