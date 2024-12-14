<?php

declare(strict_types=1);

namespace App\Application\Actions\Outbreak;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

final class ViewCRSTopAction extends Action
{
    private const CRS_TOP_VIEW = "outbreak/CRS-top.html.twig";

    protected function action(): ResponseInterface
    {
        return Twig::fromRequest($this->request)
            ->render($this->response, self::CRS_TOP_VIEW);
    }
}
