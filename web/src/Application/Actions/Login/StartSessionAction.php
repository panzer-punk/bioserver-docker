<?php

declare(strict_types=1);

namespace App\Application\Actions\Login;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

final class StartSessionAction extends Action
{
    protected function action(): ResponseInterface
    {
        $params = $this->request->getQueryParams();
        $gameID = $this->resolveArg("gameID");

        return Twig::fromRequest($this->request)
            ->render(
                $this->response,
                "startsession.html.twig",
                [
                    "gameID" => $gameID,
                    "sessid" => $params["sessid"]
                ]
            );
    }
}