<?php

declare(strict_types=1);

namespace App\Domain\Dnas;

interface Connector
{
    public function connect(string $packet): ConnectResults;
}
