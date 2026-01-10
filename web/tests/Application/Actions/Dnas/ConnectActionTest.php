<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Dnas;

use App\Domain\Dnas\DnasConnectAction;
use App\Domain\GameID;
use Slim\Psr7\Factory\StreamFactory;
use Tests\TestCase;

class ConnectActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! defined("APP_ROOT")) {
            define("APP_ROOT", __DIR__ . "/../../../../");
        }
    }

    public static function dnasPacketDataProvider(): array
    {
        $bio1 = GameID::BIO1->name;
        $bio2 = GameID::BIO2->name;

        return [
            "regular_gai-gw_success_{$bio1}" => [
                "packet" => "request_gai-gw_regular_0118000000000000_308bytes_success.bin",
                "response" => "response_gai-gw_regular_011800050008498c_328bytes_success.bin",
                "folder" => "gai-gw",
                "action" => DnasConnectAction::Connect->value,
            ],
            "others_gai-gw_success_{$bio1}" => [
                "packet" => "request_gai-gw_others_011880010008498c_184bytes_success.bin",
                "response" => "response_gai-gw_others_011880060008498c_170bytes_success.bin",
                "folder" => "gai-gw",
                "action" => DnasConnectAction::Others->value,
            ]
        ];
    }

    /**
     * @dataProvider dnasPacketDataProvider
     *
     * @param string $packetFile
     * @param string $responseFile
     * @return void
     */
    public function testConnectActionSuccess(
        string $packetFile,
        string $responseFile,
        string $folder,
        string $action
    ): void {
        $packet = (new StreamFactory())->createStreamFromFile(
            APP_ROOT . "/tests/fixtures/packets/requests/{$packetFile}"
        );
        $expectedResponse = file_get_contents(
            APP_ROOT . "/tests/fixtures/packets/responses/{$responseFile}"
        );
        $request = $this->createRequest(
            "POST",
            "/dnas/{$folder}/{$action}",
            headers: ["Content-Type" => "image/gif"],
            serverParams: [
                "REMOTE_ADDR" => "127.0.0.1",
                "REMOTE_PORT" => "6021"
            ]
        )->withBody($packet);

        $response = $this->getAppInstance()->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("image/gif", $response->getHeaderLine("Content-Type"));
        $this->assertEquals($expectedResponse, (string) $response->getBody());
    }
}
