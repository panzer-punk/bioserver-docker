<?php

declare(strict_types=1);

namespace App\Domain\Dnas;

enum DnasConnectAction: string
{
    case Connect = "connect";
    case Others = "others";
}
