<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Domain\Dnas\Connector;
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

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($settings->get("LOGGER_NAME"));

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($settings->get("LOGGER_PATH"), $settings->get("LOGGER_LEVEL"));
            $logger->pushHandler($handler);

            return $logger;
        },
    ]);
};
