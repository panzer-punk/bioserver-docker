<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Outbreak;

use App\Domain\GameID;
use Tests\Application\Actions\Outbreak\Traits\HasGameIDDataProvider;
use Tests\TestCase;

class StartSessionActionTest extends TestCase
{
    use HasGameIDDataProvider;

    /**
     * @dataProvider gameIDDataProvider
     * @param GameID $gameID
     * @return void
     */
    public function testStartSessionViewSuccess(GameID $gameID): void
    {
        $sessid = 2754228;
        $expectedSessionString = sprintf(
            '<!--<CSV>"OK","%s","https://www01.kddi-mmbb.jp/%s/enterareas","https://www01.kddi-mmbb.jp/%s/login",</CSV>-->',
            $sessid,
            $gameID->value,
            $gameID->value
        );

        $request = $this->createRequest(
            "GET", 
            "/{$gameID->value}/startsession",
            serverParams: [
                "REMOTE_ADDR" => "127.0.0.1",
                "REMOTE_PORT" => "6021"
            ]
        )->withQueryParams(["sessid" => $sessid]);
        $response = self::getApp()->handle($request);
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString("<!--result--><!--connection id--><!--start the game url--><!--exit game url-->", $body);
        $this->assertStringContainsString($expectedSessionString, $body);
    }
}