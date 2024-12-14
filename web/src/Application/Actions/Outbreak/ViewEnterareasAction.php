<?php

declare(strict_types=1);

namespace App\Application\Actions\Outbreak;

use App\Application\Actions\Action;
use App\Domain\GameID;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

final class ViewEnterareasAction extends Action
{
    private const ENTERAREAS_VIEW = "outbreak/enterareas.html.twig";

    //@todo managable areas
    private array $areas = [];

    public function __construct(
        LoggerInterface $logger
    ) {
        parent::__construct($logger);

        $this->areas = [
            GameID::BIO1->value => [
                "OK",
                "www01.kddi-mmbb.jp:8300",
                "0",
                "999",
                "0ad601082008,WEST TOWN,2",
                "<BODY><SIZE=4>Free AREA<BR><BODY>Create your own games<BR><BODY>obsrv.org<END>",
                "<BODY><SIZE=4>Not much to say<BR><BODY>have fun<BR><BODY>cu, the_fog<END>",
                "www01.kddi-mmbb.jp:8300",
                "0",
                "999",
                "0ad601082008,EAST TOWN,1",
                "<BODY><SIZE=4>Scenario Mode<BR><BODY>DEFUNC!<BR><BODY>obsrv.org<END>",
                "<BODY><SIZE=4>This mode is not<BR><BODY>fully implemented yet.<BR><BODY>cu, the_fog<END>"
            ],
            GameID::BIO2->value => [
                "OK",
                "www01.kddi-mmbb.jp:8200",
                "0",
                "999",
                "0ad601082008,ALPHA SERVER",
                "<BODY>Please select the server.<END>",
                "  ",
            ]
        ];
    }

    protected function action(): ResponseInterface
    {
        $gameID     = $this->resolveArg("gameID");
        $serverData = $this->request->getServerParams();

        $this->logger->debug("Game {$gameID}: enterareas", ["ip" => $serverData["REMOTE_ADDR"]]);

        return Twig::fromRequest($this->request)
            ->render(
                $this->response,
                self::ENTERAREAS_VIEW,
                [
                    "areas" => $this->areas[$gameID]
                ]
            );
    }
}
