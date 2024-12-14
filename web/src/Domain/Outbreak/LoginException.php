<?php

declare(strict_types=1);

namespace App\Domain\Outbreak;

use Exception;
use Throwable;

final class LoginException extends Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        Throwable|null $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
