<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Outbreak;

use App\Application\Actions\Outbreak\Handlers\RegisterHandler;
use App\Domain\GameID;
use App\Domain\Outbreak\PasswordValidatorInterface;
use App\Domain\Outbreak\UserNameValidatorInterface;
use App\Domain\Outbreak\ValueObjects\Password;
use App\Domain\Outbreak\ValueObjects\UserName;
use mysqli;
use Psr\Http\Message\ResponseInterface;
use Tests\Application\Actions\Outbreak\Traits\HasGameIDDataProvider;
use Tests\TestCase;

class LoginActionTest extends TestCase
{
    use HasGameIDDataProvider;

    private static function createUser(string $username, ?string $password = null): void
    {
        $app = self::getApp();

        /**@var mysqli $mysql */
        $mysql = $app->getContainer()->get(mysqli::class);
        /**@var PasswordValidatorInterface $passwordValidator */
        $passwordValidator = $app->getContainer()->get(PasswordValidatorInterface::class);
        /**@var UserNameValidatorInterface $usernameValidator */
        $usernameValidator = $app->getContainer()->get(UserNameValidatorInterface::class);

        (new RegisterHandler($mysql))->handle(
            new UserName($username, $usernameValidator),
            new Password($password ?? "p4ssworD", $passwordValidator)
        );
    }

    public static function invalidLoginCredentialsDataProvider(): array
    {
        $usernameValid = 'panzer_punk';
        $passwordValid = 'P_punk228';

        self::createUser($usernameValid, $passwordValid);

        $credentials = [
            'wrong_password_production' => [
                'username' => $usernameValid,
                'password' => 'P_punk229',
                'status' => 200,
                'message' => 'Login failed. Your login/password combination is wrong.'
            ],
            'wrong_username_production' => [
                'username' => 'p_punk',
                'password' => $passwordValid,
                'status' => 200,
                'message' => 'Login failed. Your login/password combination is wrong.'
            ],
        ];

        return self::credentialsWithGameID(
            array_merge($credentials, self::invalidCredentials())
        );
    }

    private static function invalidCredentials(): array
    {
        return [
            'invalid_password_production' => [
                'username' => 'username',
                'password' => 'p_punk',
                'status' => 200,
                'message' => 'Password does not match criteria.'
            ],
            'invalid_username_production' => [
                'username' => 'a1',
                'password' => 'P4ssword',
                'status' => 200,
                'message' => 'Username does not match criteria.'
            ],
            'empty_username' => [
                'username' => '',
                'password' => 'password',
                'status' => 302,
                'message' => null
            ],
            'empty_password' => [
                'username' => 'username',
                'password' => '',
                'status' => 302,
                'message' => null
            ],
            'empty_username_and_password' => [
                'username' => '',
                'password' => '',
                'status' => 302,
                'message' => null
            ],
        ];
    }

    private static function credentialsWithGameID(array $credentials): array
    {
        $res = [];

        foreach ($credentials as $name => $data) {
            foreach (GameID::cases() as $case) {
                $resName = "{$name}_{$case->name}";
                $data["gameID"] = $case;
                $res[$resName] = $data;
            }
        }

        return $res;
    }

    public static function invalidRegisterCredentialsDataProvider(): array
    {
        return self::credentialsWithGameID(
            self::invalidCredentials()
        );
    }

    /**
     * @dataProvider invalidLoginCredentialsDataProvider
     * @return void
     */
    public function testLoginActionFails(
        string $username, 
        string $password, 
        int $status,
        ?string $message,
        GameID $gameID,
    ): void {
        $this->performLoginFormRequest($username, $password, $gameID, 'manual', $status, $message);
    }

    private function performLoginFormRequest(
        string $username,
        string $password,
        GameID $gameID,
        string $loginType,
        int $expectedStatus,
        ?string $expectedMessage
    ): ResponseInterface {
        $app = self::getApp();

        $request = $this->createRequest(
            "POST",
            "/{$gameID->value}/login-form",
            headers: [
                "HTTP_ACCEPT" => "*",
            ],
            serverParams: [
                "REMOTE_ADDR" => "127.0.0.1",
                "REMOTE_PORT" => "6021"
            ]
        );
        $request = $request->withParsedBody([
            "password" => $password,
            "username" => $username,
            "login" => $loginType
        ])->withHeader("Content-Type", "application/x-www-form-urlencoded");

        $response = $app->handle($request);

        $this->assertEquals($expectedStatus, $response->getStatusCode());

        if (!empty($expectedMessage)) {
            $this->assertStringContainsString($expectedMessage, (string) $response->getBody());
        }

        return $response;
    }

    /**
     * @dataProvider invalidRegisterCredentialsDataProvider
     * @return void
     */
    public function testRegisterActionFails(
        string $username, 
        string $password, 
        int $status,
        ?string $message,
        GameID $gameID,
    ): void {
        $this->performLoginFormRequest($username, $password, $gameID, 'newaccount', $status, $message);
    }

    /**
     * @dataProvider gameIDDataProvider
     *
     * @param GameID $gameID
     * @return void
     */
    public function testLoginSuccess(GameID $gameID): void
    {
        $username = $gameID->value;
        $password = "p4ssworD";
        self::createUser($username, $password);

        $response = $this->performLoginFormRequest(
            $username,
            $password,
            $gameID,
            "manual",
            200,
            "Login successful."
        );

        /**@var mysqli $mysql */
        $mysql = self::getApp()->getContainer()->get(mysqli::class);
        $sessions = $mysql->execute_query("select * from sessions where userid = ?", [$username])->fetch_all(MYSQLI_ASSOC);
        $sessid = $sessions[0]["sessid"];

        $this->assertEquals(1, count($sessions));
        $this->assertEquals($gameID->value, $sessions[0]["gameid"]);
        $this->assertStringContainsString("<a href=\"startsession?sessid={$sessid}\">Enter lobbies</a>", (string) $response->getBody());
    }

    /**
     * @dataProvider gameIDDataProvider
     * 
     * @param GameID $gameID
     * @return void
     */
    public function testViewLoginActionSuccess(GameID $gameID): void
    {
        $request = $this->createRequest(
            "GET",
            "/{$gameID->value}/login"
        );
        /**@var PasswordValidatorInterface $passwordValidator */
        $passwordValidator = self::getApp()->getContainer()->get(PasswordValidatorInterface::class);
        /**@var UserNameValidatorInterface $usernameValidator */
        $usernameValidator = self::getApp()->getContainer()->get(UserNameValidatorInterface::class);

        $response = self::getApp()->handle($request);
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString($passwordValidator->criteria(),$body);
        $this->assertStringContainsString($usernameValidator->criteria(), $body);
    }
}