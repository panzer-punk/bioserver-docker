<?php

declare(strict_types=1);

namespace App\Application\Settings;

use Exception;
use Monolog\Logger;


class EnvSettings implements SettingsInterface
{
    private array $settings;

    public function __construct()
    {
        $production = $_ENV["PRODUCTION"] ?? false;

        $this->settings = [
            "version"             => "2.0.0-beta",
            "production"          => $production,
            "displayErrorDetails" => ! $production,
            "logError"            => $_ENV["LOG_ERROR"] ?? false,
            "logErrorDetails"     => $_ENV["LOG_ERROR_DETAILS"] ?? false,
            "logger" => [
                "name"  => "bioserver-web-ui",
                "path"  => "php://stdout",
                "level" => $_ENV["LOG_LEVEL"] ?? Logger::DEBUG
            ],
            "db" => [
                "host"     => $_ENV["DB_HOST"],
                "database" => $_ENV["DB_DATABASE"],
                "user"     => $_ENV["DB_USER"],
                "password" => $_ENV["DB_PASSWORD"]
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function get(string $key = '')
    {
        if (! isset($this->settings[$key])) {
            throw new Exception("Unknown setting $key.");
        }

        return $this->settings[$key];
    }
}
