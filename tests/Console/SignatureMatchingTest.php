<?php

namespace Curfle\Tests\Auth\JWT;


use Curfle\Chronos\Chronos;
use Curfle\Console\Command;
use Curfle\Console\Input;
use Curfle\Console\Timetable;
use Curfle\Essence\Application;
use Curfle\Support\Facades\Buddy;
use Exception;
use PHPUnit\Framework\TestCase;

class SignatureMatchingTest extends TestCase
{

    /**
     * run:{param1} {param2}
     */
    public function testMultipleParametersWithDifferentClasses()
    {
        $command = (new Command(new Application()))
            ->resolver(fn() => null)
            ->signature("run:{param1} {param2}")
            ->where("param1", "([a-z]|[A-Z]|[0-9])+")
            ->where("param2", "v?([0-9])+\.([0-9])+\.([0-9])+");

        $this->assertTrue(
            $command->matches(Input::fromString("run:core v2.1.8"))
        );
        $this->assertSame(
            ["param1" => "core", "param2" => "v2.1.8"],
            $command->getMatchedParameters()
        );
    }

    /**
     * serve {address?}
     */
    public function testServeSignature()
    {
        $command = (new Command(new Application()))
            ->resolver(fn() => null)
            ->signature("serve {address?}")
            ->where("address", "\w+(\:[0-9]+)?");

        $this->assertTrue(
            $command->matches(Input::fromString("serve"))
        );
        $this->assertEmpty($command->getMatchedParameters());

        $this->assertTrue(
            $command->matches(Input::fromString("serve localhost:2345"))
        );
        $this->assertSame(
            ["address" => "localhost:2345"],
            $command->getMatchedParameters()
        );
    }

    public function testComplexCommandWithMultipleClasses()
    {
        $command = (new Command(new Application()))
            ->resolver(fn() => null)
            ->signature("command:run {name} {options?} {password} {ip}")
            ->where("options", "[0-9]+")
            ->where("password", "(?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,}")
            ->where("ip", "([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)");

        $this->assertTrue(
            $command->matches(Input::fromString("command:run jane 2 SuperDog2 127.0.0.1"))
        );
        $this->assertSame(
            ["name" => "jane", "options" => "2", "password" => "SuperDog2", "ip" => "127.0.0.1"],
            $command->getMatchedParameters()
        );

        $this->assertTrue(
            $command->matches(Input::fromString("command:run jane SuperDog552 127.0.0.1"))
        );
        $this->assertSame(
            ["name" => "jane", "password" => "SuperDog552", "ip" => "127.0.0.1"],
            $command->getMatchedParameters()
        );
    }
}