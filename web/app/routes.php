<?php

declare(strict_types=1);

use App\Application\Actions\Dnas\ConnectAction;
use App\Application\Actions\Login\LoginAction;
use App\Application\Settings\SettingsInterface;
use App\Domain\GameID;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Views\Twig;

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

        $group->get(
            "/login", 
            function (Request $request, Response $response, array $args) {
                /**
                 * @var Container $this
                 * @var SettingsInterface $settings
                 */
                $settings = $this->get(SettingsInterface::class);
                
                $title = sprintf(
                    "%s: %s server",
                    $settings->get("version"),
                    $settings->get("production") ? "production" : "non production"
                );

                return Twig::fromRequest($request)
                    ->render(
                        $response,
                        "login.html.twig",
                        [
                            "title" => $title
                        ]
                );
            }
        );
        $group->get(
            "/startsession", 
            function (Request $request, Response $response, array $args) {
                $params = $request->getQueryParams();
                $gameID = $args["gameID"];

                return Twig::fromRequest($request)
                    ->render(
                        $response, 
                        "startsession.html.twig", 
                        [
                            "gameID" => $gameID,
                            "sessid" => $params["sessid"]
                        ]
                );
            }
        );
        $group->any(
            "/CRS-top.jsp", 
            function (Request $request, Response $response, array $args) {
                return Twig::fromRequest($request)
                    ->render($response, "CRS-top.html.twig");
            }
        );
    });

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });
};
