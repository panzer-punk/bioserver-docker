<?php

declare(strict_types=1);

namespace App\Application\Actions\Login\Handlers;

use App\Application\Actions\Login\ValueObjects\Password;
use App\Application\Actions\Login\ValueObjects\UserName;
use App\Domain\Login\LoginException;
use App\Domain\Login\LoginHandlerInterface;
use mysqli;

final class LoginHandler implements LoginHandlerInterface
{
    public function __construct(
        private mysqli $connection
    ) {
        
    }

    public function handle(UserName $username, Password $password): void
    {
        $res = mysqli_query($this->connection, 'select count(*) as cnt from users where userid="'. $username->value .'" and passwd="'. $password->value .'"');
        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);

        if ($row["cnt"] != 1) {
            throw new LoginException("Login failed. Your login/password combination is wrong.");
        }
    }
}
