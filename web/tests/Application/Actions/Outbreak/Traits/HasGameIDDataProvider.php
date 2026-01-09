<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Outbreak\Traits;

use App\Domain\GameID;

trait HasGameIDDataProvider
{
    public static function gameIDDataProvider(): array
    {
        $ids = GameID::cases();

        return array_reduce(
            $ids,
            function (array $carry, GameID $id) {
                $carry[$id->name] = [$id];

                return $carry;
            },
            []
        );
    }
}