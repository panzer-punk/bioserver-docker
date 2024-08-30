<?php

declare(strict_types=1);

namespace App\Application\Actions\Login\Handlers;

use App\Domain\Login\LoginException;
use App\Domain\Login\LoginHandlerInterface;
use App\Domain\User\User;
use mysqli;

final class RegisterHandler implements LoginHandlerInterface
{
    public function __construct(
        private mysqli $connection
    ) {
        
    }

    public function handle(string $username, string $password): User
    {
        $username = substr(preg_replace("/[^A-Za-z0-9 _]/", "", $username), 0, 14);
        $password = substr(preg_replace("/[^A-Za-z0-9 _]/", "", $password), 0, 14);

        $res = mysqli_query($this->connection, 'insert into users (userid, passwd) values("'. $username .'","'. $password .'")');

        if (! $res) {
            throw new LoginException("Registration failed.");
        }

        //@todo return real user
        return new User(1, $username, $username, $username);
    }
}
