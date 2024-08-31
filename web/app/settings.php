<?php

declare(strict_types=1);

use App\Application\Settings\EnvSettings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new EnvSettings();
        }
    ]);
};
