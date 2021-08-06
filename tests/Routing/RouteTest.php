<?php

namespace Curfle\Tests\Routing;

use Curfle\Routing\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    /**
     * Tests the ->matches function.
     */
    public function testMatches()
    {
        $route = (new Route("GET", "/users/{id}", null))
            ->where("id", "[0-9]+");

        $this->assertTrue($route->matches("GET", "/users/112624"));
        $this->assertFalse($route->matches("GET", "/users/abcdefg"));
    }

    /**
     * Tests the ->getMatchedParameters function .
     */
    public function testGetMatchedParameters()
    {
        $route = (new Route("POST", "/users/{id}/{secret}", null))
            ->where("secret", "[a-z]+")
            ->where("id", "[0-9]+");

        $this->assertTrue($route->matches("POST", "/users/2235/supersecret"));
        $this->assertSame([
            "id" => "2235",
            "secret" => "supersecret"
        ],
        $route->getMatchedParameters());
    }
}