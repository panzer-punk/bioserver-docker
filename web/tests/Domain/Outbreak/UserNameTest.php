<?php

declare(strict_types=1);

namespace Tests\Domain\Outbreak;

use App\Domain\Outbreak\UserNameValidatorInterface;
use App\Domain\Outbreak\ValueObjects\UserName;
use InvalidArgumentException;
use Tests\TestCase;

class UserNameTest extends TestCase
{
    public function testUserNameSuccess(): void
    {
        $usernameValue = "B5F Computer Room";
        $validator = new class implements UserNameValidatorInterface {
            public function valid(string $username): bool
            {
                return true;
            }

            public function criteria(): string
            {
                return "Dummy criteria.";
            }
        };


        $username = new UserName($usernameValue, $validator);

        $this->assertEquals(mb_strtolower($usernameValue), $username->value);
    }

    public function testUserNameFailure(): void
    {
        $usernameValue = "B5F Computer Room";
        $validator = new class implements UserNameValidatorInterface {
            public function valid(string $username): bool
            {
                return false;
            }

            public function criteria(): string
            {
                return "Dummy criteria.";
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Username does not match criteria.");

        new UserName($usernameValue, $validator);
    }
}