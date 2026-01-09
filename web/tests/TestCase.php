<?php

declare(strict_types=1);

namespace Tests;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;
use mysqli;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

class TestCase extends PHPUnit_TestCase
{
    use ProphecyTrait;

    /**
     * @var mysqli[]
     */
    private array $databaseConnections = [];

    /**
     * Set up test environment - start database transaction
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseConnections = [];
    }

    /**
     * Tear down test environment - rollback database transaction
     */
    protected function tearDown(): void
    {
        foreach ($this->databaseConnections as $connection) {
            if ($connection && !$connection->connect_errno) {
                $connection->rollback();
                $connection->autocommit(true);
            }
        }
        $this->databaseConnections = [];
        parent::tearDown();
    }

    /**
     * @return App
     * @throws Exception
     */
    protected function getAppInstance(): App
    {
        // Instantiate PHP-DI ContainerBuilder
        $containerBuilder = new ContainerBuilder();

        // Container intentionally not compiled for tests.

        // Set up settings
        $settings = require __DIR__ . '/../app/settings.php';
        $settings($containerBuilder);

        // Set up dependencies
        $dependencies = require __DIR__ . '/../app/dependencies.php';
        $dependencies($containerBuilder);

        // Set up repositories
        $repositories = require __DIR__ . '/../app/repositories.php';
        $repositories($containerBuilder);

        $containerBuilder->addDefinitions([
            LoggerInterface::class => function (ContainerInterface $c) {
                return new NullLogger;
            }
        ]);

        // Build PHP-DI Container instance
        $container = $containerBuilder->build();

        // Add Twig
        $twig = Twig::create(__DIR__ . "/../views", ["cache" => false]);

        // Start database transaction for test isolation
        $mysql = $container->get(mysqli::class);
        if ($mysql && !$mysql->connect_errno) {
            $mysql->autocommit(false);
            $this->databaseConnections[] = $mysql;
        }

        // Instantiate the app
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        // Register middleware
        $middleware = require __DIR__ . '/../app/middleware.php';
        $middleware($app);

        // Register routes
        $routes = require __DIR__ . '/../app/routes.php';
        $routes($app);

        $app->add(TwigMiddleware::create($app, $twig));

        return $app;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $headers
     * @param array  $cookies
     * @param array  $serverParams
     * @return Request
     */
    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $cookies = [],
        array $serverParams = []
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }
}
