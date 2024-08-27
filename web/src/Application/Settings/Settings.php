<?php

declare(strict_types=1);

namespace App\Application\Settings;

class Settings implements SettingsInterface
{
    private array $defaults = [
        "VERSION_INFO" => "2.0.0-beta",
        "VERIFY_EMAIL" => false,
        "PRODUCTION"   => false
    ];

    /**
     * @return mixed
     */
    public function get(string $key = '')
    {
        $value = getenv($key, true) ?: getenv($key);

        if (! $value && isset($this->defaults[$key])) {
            return $this->defaults[$key];
        }

        return $value;
    }
}
