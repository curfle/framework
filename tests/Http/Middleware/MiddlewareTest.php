<?php

namespace Curfle\Tests\Http\Middleware;

use Curfle\Auth\AuthenticationManager;
use Curfle\Auth\JWT\JWT;
use Curfle\Auth\Middleware\Authenticate;
use Curfle\Config\Repository;
use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Database\Schema\Blueprint;
use Curfle\Essence\Application;
use Curfle\Http\Middleware\AllowCors;
use Curfle\Http\Request;
use Curfle\Http\Response;
use Curfle\Routing\Route;
use Curfle\Routing\Router;
use Curfle\Support\Env\Env;
use Curfle\Support\Exceptions\Http\Dispatchable\HttpAccessDeniedException;
use Curfle\Support\Exceptions\Http\MiddlewareNotFoundException;
use Curfle\Support\Exceptions\Misc\SecretNotPresentException;
use Curfle\Support\Facades\Auth;
use Curfle\Support\Facades\DB;
use Curfle\Support\Facades\Facade;
use Curfle\Support\Facades\Schema;
use Curfle\Tests\Resources\DummyClasses\DAO\User;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    private Application $app;
    private Router $router;
    private MySQLConnector $connector;

    protected function setUp(): void
    {
        // set secret
        Env::set("SECRET", "d5d8984b90b15dbb8e0620170d9fe954d6b56332ed3b9388c0c957894b8edc8d");

        // create table and insert one user
        $this->connector = new MySQLConnector(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $this->connector->exec("CREATE TABLE `user` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `firstname` varchar(250) NOT NULL,
              `lastname` varchar(250) NOT NULL,
              `email` varchar(250) NOT NULL,
              `password` varchar(100) NOT NULL,
              `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `deleted` timestamp DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $this->connector->exec("INSERT INTO user (id, firstname, lastname, email, password) VALUES (42, 'Jane', 'Doe', '', '')");

        // application instance
        $this->app = new Application();
        $this->app->singleton("auth", function (Application $app) {
            return new AuthenticationManager($app);
        });
        $this->app->singleton("config", function () {
            return new Repository([
                "auth" => [
                    "guardians" => [
                        "default" => [
                            "drivers" => [
                                \Curfle\Auth\Guardians\Guardian::DRIVER_BEARER
                            ],
                            "authenticatable" => User::class,
                            "guardian" => \Curfle\Auth\Guardians\JWTGuardian::class
                        ]
                    ]
                ]
            ]);
        });
        Facade::setFacadeApplication($this->app);

        // router instance
        $this->router = new Router($this->app);

        $this->router->aliasMiddleware("cors", AllowCors::class);
        $this->router->aliasMiddleware("auth", Authenticate::class);
    }

    protected function tearDown(): void
    {
        $this->connector->exec("DROP TABLE IF EXISTS user");
    }

    /**
     * Resets the response in the container.
     */
    private function resetReponse()
    {
        $this->app->singleton("response", function () {
            return new Response();
        });
    }

    /**
     * test ::middleware()
     * @throws MiddlewareNotFoundException
     */
    public function testMiddleware()
    {
        // register new response
        $this->resetReponse();

        // create route
        $route = new Route("GET", "/", function () {
            return "Hello world!";
        });
        $route->setContainer($this->app);
        $route->setRouter($this->router);
        $route->middleware("cors");

        // create request
        $request = new Request("GET", "/");

        // resolve request
        $response = $route->resolve($request);

        // check if header has been set
        $this->assertEquals(
            ["Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Headers" => "*"],
            $response->getHeaders()
        );
    }

    /**
     * test ::middleware()
     * @throws MiddlewareNotFoundException
     */
    public function testAuthMiddlewareWithNoToken()
    {
        // register new response
        $this->resetReponse();

        // create route
        $route = new Route("GET", "/", function () {
            return "Hello world!";
        });
        $route->setContainer($this->app);
        $route->setRouter($this->router);
        $route->middleware("auth");

        // create request
        $request = new Request("GET", "/");

        // expect exception
        $this->expectException(HttpAccessDeniedException::class);

        // resolve request
        $response = $route->resolve($request);
    }

    /**
     * test ::middleware()
     * @throws MiddlewareNotFoundException
     * @throws SecretNotPresentException
     */
    public function testAuthMiddlewareWithToken()
    {
        // register new response
        $this->resetReponse();

        // create route
        $route = new Route("GET", "/", function () {
            return "Hello world!";
        });
        $route->setContainer($this->app);
        $route->setRouter($this->router);
        $route->middleware("auth");

        // generate valid token
        $token = JWT::generate(["sub" => 42]);

        // create request
        $request = new Request("GET", "/");
        $request->setHeaders([
            "Authorization" => "Bearer $token"
        ]);

        // resolve request
        $response = $route->resolve($request);

        // assert correct response
        $this->assertEquals(
            "Hello world!",
            $response->getContent()
        );

        // assert correct authenticated user
        $this->assertEquals(
            42,
            Auth::user()->id
        );
    }
}