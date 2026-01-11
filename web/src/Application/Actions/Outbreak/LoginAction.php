<?php

declare(strict_types=1);

namespace App\Application\Actions\Outbreak;

use App\Application\Actions\Action;
use App\Application\Actions\Outbreak\Handlers\LoginHandler;
use App\Application\Actions\Outbreak\Handlers\RegisterHandler;
use App\Domain\Outbreak\OutbreakLoginAction;
use App\Domain\Outbreak\ValueObjects\Password;
use App\Domain\Outbreak\ValueObjects\UserName;
use App\Domain\Outbreak\LoginException;
use App\Domain\Outbreak\LoginHandlerInterface;
use App\Domain\Outbreak\PasswordValidatorInterface;
use App\Domain\Outbreak\UserNameValidatorInterface;
use DomainException;
use Exception;
use InvalidArgumentException;
use Monolog\Logger;
use mysqli;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

final class LoginAction extends Action
{
    private const LOGIN_FAILED_VIEW = "outbreak/login-failed.html.twig";
    private const LOGIN_SUCCESSFUL_VIEW = "outbreak/login-successful.html.twig";

    public function __construct(
        LoggerInterface $logger,
        private mysqli $mysql,
        private UserNameValidatorInterface $usernameValidator,
        private PasswordValidatorInterface $passwordValidator
    ) {
        parent::__construct($logger);
    }

    protected function action(): ResponseInterface
    {
        $gameID     = $this->resolveArg("gameID");
        $data       = $this->getFormData();
        $serverData = $this->request->getServerParams();
        $handler    = $this->handler((string) $data["login"]);
        $twig       = Twig::fromRequest($this->request);

        $loginUrl  = "/{$gameID}/login";
        $crsTopUrl = "/{$gameID}/CRS-top.jsp";

        $usernameValue = $data["username"] ?? "";
        $passwordValue = $data["password"] ?? "";

        $ip       = $serverData["REMOTE_ADDR"];
        $port     = $serverData["REMOTE_PORT"];

        if (empty($passwordValue) || empty($usernameValue)) {
            $this->logger->log(Logger::DEBUG, "Game {$gameID} login: empty username or password.");

            $response = $this->response->withAddedHeader("Location", "CRS-top.jsp");
            $response = $response->withStatus(302);

            return $response;
        }

        try {
            $username = new UserName($usernameValue, $this->usernameValidator);
            $password = new Password($passwordValue, $this->passwordValidator);
            $userid   = $username->value;

            $this->logger->log(
                Logger::DEBUG,
                "Game {$gameID} login attempt, username {$username->value}",
                ["ip" => $ip]
            );

            $handler->handle($username, $password);

            $this->logger->log(
                Logger::INFO,
                "Game {$gameID} successful login, username {$username->value}",
                ["ip" => $ip]
            );

            //drop session for both games
            $stmnt = $this->mysql->prepare("delete from sessions where userid = ?");
            $stmnt->bind_param("s", $userid);
            $res = $stmnt->execute();

            if (! $res) {
                throw new DomainException("Session creation failed.");
            }

            $sessid = $this->sessionID($gameID);
            $stmnt = $this->mysql->prepare(
                "insert into sessions (userid, ip, port, sessid, lastlogin, gameid) values (?, ?, ?, ?, now(), ?)"
            );
            $stmnt->bind_param("ssiss", $userid, $ip, $port, $sessid, $gameID);
            $res = $stmnt->execute();

            if (! $res) {
                throw new DomainException("Session creation failed.");
            }

            $this->logger->info(
                "Game {$gameID} session {$sessid} created",
                ["username" => $username->value, "ip" => $ip]
            );
        } catch (LoginException | InvalidArgumentException $e) {
            //@todo refactor logs
            $this->logger->log(Logger::ERROR, "Game {$gameID} login failed: {$e->getMessage()}", ["ip" => $ip]);

            return $twig->render(
                $this->response,
                self::LOGIN_FAILED_VIEW,
                [
                    "message" => $e->getMessage(),
                    "url"     => $crsTopUrl
                ]
            );
        } catch (DomainException $e) {
            $this->logger->log(Logger::ERROR, "Game {$gameID}: {$e->getMessage()}", ["ip" => $ip]);

            return $twig->render(
                $this->response,
                self::LOGIN_FAILED_VIEW,
                [
                    "message" => $e->getMessage(),
                    "url"     => $loginUrl
                ]
            );
        } catch (Exception $e) {
            $this->logger->log(
                Logger::ERROR,
                "Game {$gameID}: {$e->getMessage()}",
                [
                    "ip" => $ip,
                    "stack_trace" => $e->getTraceAsString()
                ]
            );

            return $twig->render(
                $this->response,
                self::LOGIN_FAILED_VIEW,
                [
                    "message" => "Unknown error.",
                    "url"     => $loginUrl
                ]
            );
        }

        return $twig->render(
            $this->response,
            self::LOGIN_SUCCESSFUL_VIEW,
            [
                "sessid" => $sessid
            ]
        );
    }

    private function handler(string $action): LoginHandlerInterface
    {
        $action = OutbreakLoginAction::from($action);

        return match ($action) {
            OutbreakLoginAction::NewAccount => new RegisterHandler($this->mysql),
            OutbreakLoginAction::Manual => new LoginHandler($this->mysql)
        };
    }

    private function sessionID(string $gameID): int
    {
        while (true) {
            $sessid = mt_rand(10000000, 99999999);

            $res = $this->mysql->query(
                sprintf(
                    "select count(*) as cnt from sessions where sessid = %s and gameid = %s",
                    $sessid,
                    $gameID
                )
            );

            if ($res !== false && $res->num_rows > 0) {
                $row = $res->fetch_array(MYSQLI_ASSOC);

                if (isset($row["cnt"]) && $row["cnt"] == 0) {
                    return $sessid;
                }
            }
        }
    }
}
