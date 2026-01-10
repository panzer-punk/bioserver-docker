<?php

declare(strict_types=1);

use App\Application\Actions\Dnas\ConnectAction;
use App\Application\Actions\Outbreak\ViewEnterareasAction;
use App\Application\Actions\Outbreak\LoginAction;
use App\Application\Actions\Outbreak\StartSessionAction;
use App\Application\Actions\Outbreak\ViewCRSTopAction;
use App\Application\Actions\Outbreak\ViewLoginAction;
use App\Domain\Dnas\DnasConnectAction;
use App\Domain\GameID;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->group('/dnas', function (Group $group) {
        $actions = implode(
            "|",
            array_column(DnasConnectAction::cases(), "value")
        );

        $group->post("/{folder}/{action:{$actions}}", ConnectAction::class);
    });

    $gameIDs = implode(
        "|", 
        array_column(GameID::cases(), "value")
    );

    $app->group("/{gameID:{$gameIDs}}", function (Group $group) {
        $group->post("/login-form", LoginAction::class);
        $group->get("/login", ViewLoginAction::class);
        $group->get("/startsession", StartSessionAction::class);
        $group->any("/CRS-top.jsp", ViewCRSTopAction::class);
        $group->any("/enterareas", ViewEnterareasAction::class);
    });
};
