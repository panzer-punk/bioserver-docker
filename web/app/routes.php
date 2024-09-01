<?php

declare(strict_types=1);

use App\Application\Actions\Connect\ViewEnterareasAction;
use App\Application\Actions\Dnas\ConnectAction;
use App\Application\Actions\Login\LoginAction;
use App\Application\Settings\SettingsInterface;
use App\Domain\GameID;
use App\Domain\Login\PasswordValidatorInterface;
use App\Domain\Login\UserNameValidatorInterface;
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
                
                $passwordValidator = $this->get(PasswordValidatorInterface::class);
                $usernameValidator = $this->get(UserNameValidatorInterface::class);

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
                            "title" => $title,
                            "passwordCriteria" => $passwordValidator->criteria(),
                            "usernameCriteria" => $usernameValidator->criteria()
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
        $group->any("/enterareas", ViewEnterareasAction::class);
    });

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });
};
