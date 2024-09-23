<?php

declare(strict_types=1);

namespace App\Application\Actions\Dnas;

use App\Application\Actions\Action;
use App\Application\Actions\Dnas\Connectors\OthersConnector;
use App\Application\Actions\Dnas\Connectors\RegularConnector;
use Exception;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

class ConnectAction extends Action
{
    protected function action(): ResponseInterface
    {
        $ip        = $this->request->getServerParams()["REMOTE_ADDR"];
        $packet    = $this->request->getBody()->getContents();
        $connector = $this->connector();

        $this->logger->log(Logger::INFO, "DNAS verification started: {$this->request->getUri()}", ["ip" => $ip, "connector" => $connector::class]);

        $res     = $connector->connect($packet);

        $this->response->getBody()->write($res->content);

        return $this->response->withHeader("Content-Type", $res->contentType)
            ->withHeader("Content-Length", $res->contentLength);
    }

    private function connector()
    {
        $action = $this->resolveArg("action");
        $folder = $this->resolveArg("folder");

        $path = APP_ROOT . "/storage/dnas/{$folder}";

        return match ($action) {
            "connect" => new RegularConnector($this->logger, $path),
            "others"  => new OthersConnector($this->logger, $path)
        };
    }
}
