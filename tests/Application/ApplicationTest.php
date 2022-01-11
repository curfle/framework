<?php

namespace Curfle\Tests\Application;

use Curfle\Database\Connectors\SQLiteConnector;
use Curfle\Essence\Application;
use Curfle\Support\Exceptions\Misc\BindingResolutionException;
use Curfle\Support\Exceptions\Misc\CircularDependencyException;
use Curfle\Tests\Resources\DummyClasses\ClassWithDependencies;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class ApplicationTest extends TestCase
{
    /**
     * Tests app instance creation.
     *
     */
    public function testAppInstance()
    {
        $app = new Application();
        $this->assertSame($app, $app->make("app"));
    }

    /**
     * Tests $app->bind().
     *
     */
    public function testBind()
    {
        $app = new Application();
        $app->bind("ApplicationTest", ApplicationTest::class);
        $this->assertNotSame($app->make("ApplicationTest"), $app->make("ApplicationTest"));
    }

    /**
     * Tests $app->singleton().
     *
     */
    public function testSingleton()
    {
        $app = new Application();
        $app->singleton("ApplicationTest", ApplicationTest::class);
        $this->assertSame($app->make("ApplicationTest"), $app->make("ApplicationTest"));
    }

    /**
     * Tests app's singleton resolver.
     *
     */
    public function testSingletonResolve()
    {
        $app = new Application();
        $app->singleton("db", function(){
            return new SQLiteConnector(DB_SQLITE_FILENAME);
        });

        $this->assertTrue($app->make("db") instanceof SQLiteConnector);
    }

    /**
     * Tests $app->singleton().
     *
     */
    public function testDependencyInjection()
    {
        $app = new Application();
        $app->bind(ClassWithDependencies::class);

        $instance = $app->make(ClassWithDependencies::class);

        self::assertSame($app, $instance->getApp());
    }
}