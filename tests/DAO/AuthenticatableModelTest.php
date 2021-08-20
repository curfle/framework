<?php

namespace Curfle\Tests\DAO;


use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Config\Repository;
use Curfle\DAO\Model;
use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Database\Schema\Blueprint;
use Curfle\Database\Schema\BuilderColumn;
use Curfle\Database\Schema\ForeignKeyConstraint;
use Curfle\Database\Schema\MySQLSchemaBuilder;
use Curfle\Essence\Application;
use Curfle\Hash\HashManager;
use Curfle\Support\Facades\Facade;
use Curfle\Support\Facades\Hash;
use Curfle\Tests\Resources\DummyClasses\DAO\Job;
use Curfle\Tests\Resources\DummyClasses\DAO\Login;
use Curfle\Tests\Resources\DummyClasses\DAO\Role;
use Curfle\Tests\Resources\DummyClasses\DAO\User;
use PHPUnit\Framework\TestCase;

class AuthenticatableModelTest extends TestCase
{
    private SQLConnectorInterface $connector;
    private MySQLSchemaBuilder $builder;
    private Application $app;

    public function __construct()
    {
        parent::__construct();
        $this->connector = new MySQLConnector(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $this->builder = new MySQLSchemaBuilder($this->connector);
    }

    protected function setUp(): void
    {
        $this->app = new Application();
        $this->app->singleton("hash", function(){
            return new HashManager();
        });
        $this->app->singleton("config", function(){
            return new Repository([
                "hashing" => ["driver" => "bcrypt"]
            ]);
        });
        Facade::setFacadeApplication($this->app);
        
        $this->builder->dropIfExists("user_role");
        $this->builder->dropIfExists("login");
        $this->builder->dropIfExists("user");
        $this->builder->dropIfExists("job");
        $this->builder->dropIfExists("role");

        $this->builder->create("job", function (Blueprint $table) {
            $table->id("id");
            $table->string("name");
        });

        $this->builder->create("user", function (Blueprint $table) {
            $table->id("id");
            $table->string("firstname", 250);
            $table->string("lastname", 250);
            $table->string("email", 250);
            $table->string("password", 250);
            $table->int("job_id")->unsigned()->nullable();
            $table->timestamp("created")->defaultCurrent()->defaultCurrentOnUpdate();
            $table->foreign("job_id")
                ->references("id")
                ->on("job")
                ->onDelete(ForeignKeyConstraint::CASCADE);
            $table->softDeletes();
        });

        $this->builder->create("login", function (Blueprint $table) {
            $table->id("id");
            $table->int("user_id")->unsigned()->nullable();
            $table->timestamp("timestamp")->defaultCurrent();
            $table->foreign("user_id")
                ->references("id")
                ->on("user");
        });

        $this->builder->create("role", function (Blueprint $table) {
            $table->id("id");
            $table->string("name");
        });

        $this->builder->create("user_role", function (Blueprint $table) {
            $table->bigInt("id")->autoincrement()->primary();
            $table->int("user_id")->unsigned();
            $table->int("role_id")->unsigned();
            $table->foreign("user_id")
                ->references("id")
                ->on("user");
            $table->foreign("role_id")
                ->references("id")
                ->on("role");
        });

        Model::$connector = $this->connector;
    }

    protected function tearDown(): void
    {
        $this->builder->dropIfExists("user_role");
        $this->builder->dropIfExists("login");
        $this->builder->dropIfExists("user");
        $this->builder->dropIfExists("job");
        $this->builder->dropIfExists("role");
    }

    /**
     * Tests the ::attempt() function.
     */
    public function testAttemptSuccess()
    {
        User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "password" => Hash::hash("supersecret"),
            "email" => "jane.doe@example.dd"
        ]);

        $this->assertCount(1, User::all());
        $this->assertTrue(User::attempt([
            "email" => "jane.doe@example.dd",
            "password" => "supersecret"
        ]));
    }

    /**
     * Tests the ::attempt() function.
     */
    public function testAttemptFailure()
    {
        User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "password" => Hash::hash("supersecret"),
            "email" => "jane.doe@example.dd"
        ]);

        $this->assertCount(1, User::all());
        $this->assertFalse(User::attempt([
            "email" => "jane.doe@example.dd",
            "password" => "other_password"
        ]));
    }
}