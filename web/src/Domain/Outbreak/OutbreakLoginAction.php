<?php

declare(strict_types=1);

namespace App\Domain\Outbreak;

enum OutbreakLoginAction: string
{
    case NewAccount = "newaccount";
    case Manual = "manual";
}
