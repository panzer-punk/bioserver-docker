<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Outbreak;

use App\Domain\GameID;
use Tests\Application\Actions\Outbreak\Traits\HasGameIDDataProvider;
use Tests\TestCase;

class ViewCRSTopActionTest extends TestCase
{
    use HasGameIDDataProvider;

    /**
     * @dataProvider gameIDDataProvider
     *
     * @param GameID $gameID
     * @return void
     */
    public function testViewCRSTopActionTest(GameID $gameID): void
    {
        $request = $this->createRequest(
            "GET",
            "/{$gameID->value}/CRS-top.jsp"
        );

        $response = self::getApp()->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString(
            "<meta http-equiv=\"Refresh\" content=\"1; url=/{$gameID->value}/login\">",
            (string) $response->getBody()
        );
    }
}