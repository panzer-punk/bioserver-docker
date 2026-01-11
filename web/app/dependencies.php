<?php

declare(strict_types=1);

use App\Application\Actions\Outbreak\Validators\Password\DevPasswordValidator;
use App\Application\Actions\Outbreak\Validators\Password\ProductionPasswordValidator;
use App\Application\Actions\Outbreak\Validators\UserName\DevUserNameValidator;
use App\Application\Actions\Outbreak\Validators\UserName\ProductionUserNameValidator;
use App\Application\Settings\SettingsInterface;
use App\Domain\Outbreak\PasswordValidatorInterface;
use App\Domain\Outbreak\UserNameValidatorInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get("logger");
            $logger = new Logger($loggerSettings["name"]);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings["path"], $loggerSettings["level"]);
            $logger->pushHandler($handler);

            return $logger;
        },
        mysqli::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $db = $settings->get("db");

            $connection = mysqli_connect(
                $db["host"],
                $db["user"],
                $db["password"],
                $db["database"]
            );

            if (! $connection) {
                throw new DomainException("Couldn't establish connection to database.");
            }

            return $connection;
        },
        PasswordValidatorInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $production = $settings->get("production") && ! $settings->get("force_dev_login");

            return $production
                ? new ProductionPasswordValidator()
                : new DevPasswordValidator();
        },
        UserNameValidatorInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $production = $settings->get("production") && ! $settings->get("force_dev_login");

            return $production
                ? new ProductionUserNameValidator()
                : new DevUserNameValidator();
        }
    ]);
};
