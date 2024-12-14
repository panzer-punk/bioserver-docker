<?php

declare(strict_types=1);

namespace App\Application\Settings;

use Exception;
use Monolog\Logger;


class EnvSettings implements SettingsInterface
{
    private array $settings;
    private array $aliases = [
        "PRODUCTION" => "APP_PRODUCTION_BUILD"
    ];

    public function __construct()
    {
        $production = filter_var($_ENV["APP_PRODUCTION_BUILD"] ?? false, FILTER_VALIDATE_BOOL);

        $this->settings = [
            "version"             => "2.0.0-beta",
            "production"          => $production,
            "displayErrorDetails" => ! $production,
            "logError"            => filter_var($_ENV["LOG_ERROR"] ?? false, FILTER_VALIDATE_BOOL),
            "logErrorDetails"     => filter_var($_ENV["LOG_ERROR_DETAILS"] ?? false, FILTER_VALIDATE_BOOL),
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
        $key = isset($this->aliases[$key]) ? $this->aliases[$key] : $key;

        if (! isset($this->settings[$key])) {
            throw new Exception("Unknown setting $key.");
        }

        return $this->settings[$key];
    }
}
