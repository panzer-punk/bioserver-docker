<?php

declare(strict_types=1);

namespace App\Domain\Login;

use App\Domain\User\User;

interface LoginHandlerInterface
{
    /**
     * @throws LoginException
     * @param string $username
     * @param string $password
     * @return User
     */
    public function handle(string $username, string $password): User;
}
