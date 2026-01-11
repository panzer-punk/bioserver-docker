<?php

declare(strict_types=1);

namespace App\Application\Actions\Dnas;

use App\Application\Actions\Action;
use App\Application\Actions\Dnas\Connectors\OthersConnector;
use App\Application\Actions\Dnas\Connectors\RegularConnector;
use App\Domain\Dnas\Connector;
use App\Domain\Dnas\DnasConnectAction;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;

class ConnectAction extends Action
{
    protected function action(): ResponseInterface
    {
        $ip        = $this->request->getServerParams()["REMOTE_ADDR"];
        $packet    = $this->request->getBody()->getContents();
        $connector = $this->connector();

        $this->logger->log(
            Logger::INFO,
            "DNAS verification started: {$this->request->getUri()}",
            [
                "ip" => $ip,
                "connector" => $connector::class
            ]
        );

        $res = $connector->connect($packet);

        $this->logger->log(Logger::DEBUG, "DNAS packet value", ["value" => $res->content, "ip" => $ip]);

        $bytes = $this->response->getBody()->write($res->content);

        $this->logger->log(
            Logger::DEBUG,
            "Packet data",
            [
                "ip" => $ip,
                "Content-Type" => $res->contentType,
                "Content-Length" => $res->contentLength,
                "bytes" => $bytes
            ]
        );

        return $this->response
            ->withHeader("Content-Type", $res->contentType)
            ->withHeader("Content-Length", $res->contentLength)
            ->withProtocolVersion("1.0")
            ->withStatus(200, "OK");
    }

    private function connector(): Connector
    {
        $action = DnasConnectAction::from((string) $this->resolveArg("action"));
        $folder = (string) $this->resolveArg("folder");

        $path = APP_ROOT . "/storage/dnas/{$folder}";

        return match ($action) {
            DnasConnectAction::Connect => new RegularConnector($this->logger, $path),
            DnasConnectAction::Others  => new OthersConnector($this->logger, $path),
        };
    }
}
