<?php

declare(strict_types=1);

namespace App\Application\Actions\Dnas;

use App\Application\Actions\Action;
use App\Application\Actions\Dnas\Connectors\OthersConnector;
use App\Application\Actions\Dnas\Connectors\RegularConnector;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

class ConnectAction extends Action
{
    protected function action(): ResponseInterface
    {
        $packet    = $this->request->getBody()->getContents();
        $res       = $this->connector()->connect($packet);

        $content = fopen("php://temp", "r+");

        if ($content === false) {
            throw new Exception("Error preparing DNAS response.");
        }

        fwrite($content, $res->content);
        rewind($content);

        return new Response(
            200,
            new Headers([
                "Content-Type"   => $res->contentType,
                "Content-Length" => $res->contentLength
            ]),
            new Stream($content)
        );
    }

    private function connector()
    {
        $action = $this->resolveArg("action");
        $folder = $this->resolveArg("folder");

        $path = __DIR__ . "/storage/dnas/{$folder}";

        return match ($action) {
            "connect" => new RegularConnector($path),
            "others"  => new OthersConnector($path)
        };
    }
}
