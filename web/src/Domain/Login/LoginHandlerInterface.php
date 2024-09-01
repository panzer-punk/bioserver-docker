<?php

declare(strict_types=1);

namespace App\Domain\Login;

use App\Application\Actions\Login\ValueObjects\Password;
use App\Application\Actions\Login\ValueObjects\UserName;

interface LoginHandlerInterface
{
    /**
     * @throws LoginException
     */
    public function handle(UserName $username, Password $password): void;
}
