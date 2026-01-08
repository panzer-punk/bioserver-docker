<?php

declare(strict_types=1);

namespace App\Domain\Outbreak;

use App\Domain\Outbreak\ValueObjects\Password;
use App\Domain\Outbreak\ValueObjects\UserName;

interface LoginHandlerInterface
{
    /**
     * @throws LoginException
     */
    public function handle(UserName $username, Password $password): void;
}
