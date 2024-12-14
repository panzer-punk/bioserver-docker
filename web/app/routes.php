<?php

declare(strict_types=1);

use App\Application\Actions\Connect\ViewEnterareasAction;
use App\Application\Actions\Dnas\ConnectAction;
use App\Application\Actions\Login\LoginAction;
use App\Application\Actions\Login\StartSessionAction;
use App\Application\Actions\Login\ViewCRSTopAction;
use App\Application\Actions\Login\ViewLoginAction;
use App\Domain\GameID;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->group('/dnas', function (Group $group) {
        $group->post("/{folder}/{action:connect|others}", ConnectAction::class);
    });

    $gameIDs = implode(
        "|", 
        array_column(GameID::cases(), "value")
    );

    $app->group("/{gameID:{$gameIDs}}", function (Group $group) {
        $group->post("/login", LoginAction::class);
        $group->get("/login", ViewLoginAction::class);
        $group->get("/startsession", StartSessionAction::class);
        $group->any("/CRS-top.jsp", ViewCRSTopAction::class);
        $group->any("/enterareas", ViewEnterareasAction::class);
    });
};
