<?php

declare(strict_types=1);

namespace App\Application\Actions\Login;

use App\Application\Actions\Action;
use App\Application\Actions\Login\Handlers\LoginHandler;
use App\Application\Actions\Login\Handlers\RegisterHandler;
use App\Domain\Login\LoginException;
use App\Domain\Login\LoginHandlerInterface;
use DomainException;
use Exception;
use Monolog\Logger;
use mysqli;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

final class LoginAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private mysqli $connection
    ) {
        parent::__construct($logger);
    }

    protected function action(): ResponseInterface
    {
        $gameID     = $this->resolveArg("gameID");
        $data       = $this->getFormData();
        $serverData = $this->request->getServerParams();
        $handler    = $this->handler($data["login"]);
        $twig       = Twig::fromRequest($this->request);

        $username = $data["username"];
        $password = $data["password"];
        $ip       = $serverData["REMOTE_ADDR"];
        $port     = $serverData["REMOTE_PORT"];

        if (empty($password) || empty($username)) {
            $this->logger->log(Logger::DEBUG, "Game {$gameID} login: empty username or password.");

            $response = $this->response->withAddedHeader("Location", "CRS-top.jsp");
            $response = $response->withStatus(302);

            return $response;
        }

        try {
            $this->logger->log(Logger::DEBUG, "Game {$gameID} login attempt, username {$username}", ["ip" => $ip]);

            $handler->handle($username, $password);

            $this->logger->log(Logger::INFO, "Game {$gameID} successful login, username {$username}", ["ip" => $ip]);

            //@todo prepared statements
            //drop session for both games
            mysqli_query($this->connection, 'delete from sessions where lower(userid) = lower("' . $data["username"] . '")');

            $sessid = $this->sessionID($gameID);
            $res    = mysqli_query($this->connection, 'insert into sessions (userid,ip,port,sessid,lastlogin,gameid) values(lower("' . $username . '"),"'. $ip .'","' . $port . '","'. $sessid . '",now(),"' . $gameID . '")');
    
            if (! $res) {
                throw new DomainException("Session creation failed.");
            }

            $this->logger->info("Game {$gameID} session {$sessid} created", ["ip" => $ip]);

        } catch (LoginException $e) {
            //@todo refactor logs
            $this->logger->log(Logger::ERROR, "Game {$gameID} login failed: {$e->getMessage()}", ["username" => $username, "ip" => $ip]);
            
            return $twig->render(
                $this->response,
                "login-failed.html.twig",
                [
                    "message" => $e->getMessage(),
                    "url"     => "CRS-top.jsp"
                ]
            );
        } catch (DomainException $e) {
            $this->logger->log(Logger::ERROR, "Game {$gameID}: {$e->getMessage()}", ["ip" => $ip]);

            return $twig->render(
                $this->response,
                "login-failed.html.twig",
                [
                    "message" => $e->getMessage(),
                    "url"     => "login"
                ]
            );
        } catch (Exception $e) {
            $this->logger->log(Logger::ERROR, "Game {$gameID}: {$e->getMessage()}", ["ip" => $ip, "stack_trace" => $e->getTraceAsString()]);

            return $twig->render(
                $this->response,
                "login-failed.html.twig",
                [
                    "message" => "Unknown error.",
                    "url"     => "login"
                ]
            );
        }

        return $twig->render(
            $this->response,
            "login-successful.html.twig",
            [
                "sessid" => $sessid
            ]
        );
    }

    private function handler(string $action): LoginHandlerInterface
    {
        return match ($action) {
            "newaccount" => new RegisterHandler($this->connection),
            "manual"     => new LoginHandler($this->connection)
        };
    }

    private function sessionID(string $gameID): int
    {
        while (true) {
            $sessid = mt_rand(10000000,99999999);

            $res = mysqli_query($this->connection, 'select count(*) as cnt from sessions where sessid='. $sessid . ' and gameid = "' . $gameID . '"');
            $row = mysqli_fetch_array($res, MYSQLI_ASSOC);

            if($row["cnt"] == 0) {
                return $sessid;
            }
        }
    }
}
