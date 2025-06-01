<?php

declare(strict_types=1);

namespace App\Domain\Outbreak;

use App\Application\Actions\Outbreak\ValueObjects\Password;
use App\Application\Actions\Outbreak\ValueObjects\UserName;

interface LoginHandlerInterface
{
    /**
     * @throws LoginException
     */
    public function handle(UserName $username, Password $password): void;
}
