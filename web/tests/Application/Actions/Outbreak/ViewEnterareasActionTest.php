<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Outbreak;

use App\Application\Actions\Outbreak\ViewEnterareasAction;
use App\Domain\GameID;
use Slim\Views\Twig;
use Tests\Application\Actions\Outbreak\Traits\HasGameIDDataProvider;
use Tests\TestCase;

class ViewEnterareasActionTest extends TestCase
{
    use HasGameIDDataProvider;

    /**
     * @dataProvider gameIDDataProvider
     *
     * @param GameID $gameID
     * @return void
     */
    public function testViewEnterareasSuccess(GameID $gameID): void
    {
        $request = $this->createRequest(
            "GET",
            "/{$gameID->value}/enterareas",
            serverParams: [
                "REMOTE_ADDR" => "127.0.0.1"
            ]
        );
        $expectedView = Twig::create(__DIR__ . "/../../../../views", ["cache" => false])
            ->fetch(
                "outbreak/enterareas.html.twig", 
                ["areas" => ViewEnterareasAction::getAreas()[$gameID->value]]
            );

        $response = self::getApp()->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedView, (string) $response->getBody());
    }
}
