<?php

declare(strict_types=1);

namespace App\Application\Actions\Outbreak;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

final class StartSessionAction extends Action
{
    private const START_SESSION_VIEW = "outbreak/startsession.html.twig";

    protected function action(): ResponseInterface
    {
        $params = $this->request->getQueryParams();
        $gameID = $this->resolveArg("gameID");

        return Twig::fromRequest($this->request)
            ->render(
                $this->response,
                self::START_SESSION_VIEW,
                [
                    "gameID" => $gameID,
                    "sessid" => $params["sessid"]
                ]
            );
    }
}