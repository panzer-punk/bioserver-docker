<?php

declare(strict_types=1);

namespace App\Application\Actions\Outbreak\Handlers;

use App\Application\Actions\Outbreak\ValueObjects\Password;
use App\Application\Actions\Outbreak\ValueObjects\UserName;
use App\Domain\Outbreak\LoginException;
use App\Domain\Outbreak\LoginHandlerInterface;
use mysqli;

final class LoginHandler implements LoginHandlerInterface
{
    public function __construct(
        private mysqli $mysql
    ) { }

    public function handle(UserName $username, Password $password): void
    {
        $userid = $username->value;
        $passwd = $password->value;

        $stmnt = $this->mysql->prepare("select count(*) as cnt from users where userid = ? and passwd = ?");
        $stmnt->bind_param("ss", $userid, $passwd);
        $stmnt->execute();
        
        $res = $stmnt->get_result();

        if ($res !== false && $res->num_rows > 0) {
            $row = $res->fetch_array(MYSQLI_ASSOC);

            if (!isset($row["cnt"]) || $row["cnt"] != 1) {
                throw new LoginException("Login failed. Your login/password combination is wrong.");
            }
        }
    }
}
