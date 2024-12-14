<?php

declare(strict_types=1);

namespace App\Application\Actions\Login;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

final class ViewCRSTopAction extends Action
{
    protected function action(): ResponseInterface
    {
        return Twig::fromRequest($this->request)
            ->render($this->response, "CRS-top.html.twig");
    }
}
