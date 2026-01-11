<?php

declare(strict_types=1);

namespace App\Domain\Dnas;

use App\Domain\DomainException\DomainException;

final class DnasPacketException extends DomainException
{
    public function __construct()
    {
        parent::__construct("Unable to form DNAS packet.");
    }
}
