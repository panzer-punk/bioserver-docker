<?php

declare(strict_types=1);

namespace App\Domain;

use TypeError;
use ValueError;

enum EnvironmentType: string
{
    case Production = "production";
    case Development = "development";

    /**
     * @throws ValueError
     * @throws TypeError
     *
     * @return boolean
     */
    public static function isProduction(): bool
    {
        return self::Production === self::get();
    }

    /**
     * @throws ValueError
     * @throws TypeError
     *
     * @return boolean
     */
    public static function isDevelopment(): bool
    {
        return ! self::isProduction();
    }

    /**
     * @throws ValueError
     * @throws TypeError
     *
     * @return self
     */
    public static function get(): self
    {
        return self::from((string) $_ENV["APP_ENV"]);
    }
}
