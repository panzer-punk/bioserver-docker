<?php

declare(strict_types=1);

namespace App\Application\Actions\Dnas\Connectors;

use App\Domain\Dnas\Connector;
use App\Domain\Dnas\ConnectResults;
use App\Domain\Dnas\DnasPacketException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class OthersConnector implements Connector
{
    public function __construct(
        private LoggerInterface $logger,
        private string $basePath
    ) {
    }

    public function connect(string $packet): ConnectResults
    {
        $basePath = $this->basePath;
        $gameID   = substr($packet, 0x1b, 8);
        $qrytype  = substr($packet, 0, 4);
        $fname    = bin2hex($gameID) . "_" . bin2hex($qrytype);

        $packetPath = "{$basePath}/packets/{$fname}";
        $this->logger->log(Logger::INFO, "Reading packet: {$packetPath}");

        if (file_exists($packetPath)) {
            $packet = file_get_contents($packetPath);
        } else {
            $this->logger->log(Logger::WARNING, "{$packetPath} no such file");

            $packet = file_get_contents("{$basePath}/error.raw");
        }

        if ($packet === false) {
            throw new DnasPacketException();
        }

        return new ConnectResults("image/gif", strlen($packet), $packet);
    }
}
