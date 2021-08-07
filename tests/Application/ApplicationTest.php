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
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException
     */
    public function testAppInstance()
    {
        $app = new Application();
        $this->assertSame($app, $app->resolve("app"));
    }

    /**
     * Tests $app->bind().
     *
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException
     */
    public function testBind()
    {
        $app = new Application();
        $app->bind("ApplicationTest", ApplicationTest::class);
        $this->assertNotSame($app->resolve("ApplicationTest"), $app->resolve("ApplicationTest"));
    }

    /**
     * Tests $app->singleton().
     *
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException
     */
    public function testSingleton()
    {
        $app = new Application();
        $app->singleton("ApplicationTest", ApplicationTest::class);
        $this->assertSame($app->resolve("ApplicationTest"), $app->resolve("ApplicationTest"));
    }

    /**
     * Tests app's singleton resolver.
     *
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException
     */
    public function testSingletonResolve()
    {
        $app = new Application();
        $app->singleton("db", function(){
            return new SQLiteConnector(
                __DIR__ . "/../Resources/Database/database.db"
            );
        });

        $this->assertTrue($app->resolve("db") instanceof SQLiteConnector);
    }

    /**
     * Tests $app->singleton().
     *
     * @throws BindingResolutionException|CircularDependencyException|ReflectionException
     */
    public function testDependencyInjection()
    {
        $app = new Application();
        $app->bind(ClassWithDependencies::class);

        $instance = $app->make(ClassWithDependencies::class);

        self::assertSame($app, $instance->getApp());
    }
}