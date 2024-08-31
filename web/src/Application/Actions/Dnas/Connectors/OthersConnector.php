<?php

declare(strict_types=1);

namespace App\Application\Actions\Dnas\Connectors;

use App\Domain\Dnas\Connector;
use App\Domain\Dnas\ConnectResults;
use App\Domain\Dnas\DnasPacketException;

final class OthersConnector implements Connector
{
    public function __construct(
        private string $basePath
    ) {
        
    }

    public function connect(string $packet): ConnectResults
    {
        $basePath = $this->basePath;
        $gameID   = substr($packet, 0x1b, 8);
        $qrytype  = substr($packet, 0, 4);
        $fname    = bin2hex($gameID)."_".bin2hex($qrytype);

        if (file_exists("{$basePath}/packets/{$fname}")) {
            $packet = file_get_contents("{$basePath}/packets/{$fname}");
        } else {
            $packet = file_get_contents("{$basePath}/error.raw");
        }

        if ($packet === false) {
            throw new DnasPacketException;
        }

        return new ConnectResults("image/gif", strlen($packet), $packet);
    }
}
