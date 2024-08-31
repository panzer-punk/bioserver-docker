<?php

declare(strict_types=1);

namespace App\Application\Actions\Login\Handlers;

use App\Domain\Login\LoginException;
use App\Domain\Login\LoginHandlerInterface;
use App\Domain\User\User;
use mysqli;

final class LoginHandler implements LoginHandlerInterface
{
    public function __construct(
        private mysqli $connection
    ) {
        
    }

    public function handle(string $username, string $password): void
    {
        $username  = substr(preg_replace("/[^A-Za-z0-9 _]/", "", $username), 0, 14);
        $password  = substr(preg_replace("/[^A-Za-z0-9 _]/", "", $password), 0, 14);
        $password  = md5($password);

        $res = mysqli_query($this->connection, 'select count(*) as cnt from users where userid="'. $username .'" and passwd="'. $password .'"');
        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);

        if ($row["cnt"] != 1) {
            throw new LoginException("Login failed. Your login/password combination is wrong.");
        }
    }
}
