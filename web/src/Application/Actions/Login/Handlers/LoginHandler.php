<?php

declare(strict_types=1);

namespace App\Application\Actions\Login\Handlers;

use App\Application\Actions\Login\ValueObjects\Password;
use App\Application\Actions\Login\ValueObjects\UserName;
use App\Domain\Login\LoginException;
use App\Domain\Login\LoginHandlerInterface;
use mysqli;

final class LoginHandler implements LoginHandlerInterface
{
    public function __construct(
        private mysqli $mysql
    ) {
        
    }

    public function handle(UserName $username, Password $password): void
    {
        $userid = $username->value;
        $passwd = $password->value;

        $stmnt = $this->mysql->prepare("select count(*) as cnt from users where userid = ? and passwd = ?");
        $stmnt->bind_param("ss", $userid, $passwd);
        $stmnt->execute();
        
        $row = $stmnt->get_result()
            ->fetch_array(MYSQLI_ASSOC);

        if ($row["cnt"] != 1) {
            throw new LoginException("Login failed. Your login/password combination is wrong.");
        }
    }
}
