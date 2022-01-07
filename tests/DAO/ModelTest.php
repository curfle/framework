<?php

namespace Curfle\Tests\DAO;


use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Container\Container;
use Curfle\DAO\Model;
use Curfle\DAO\Relationships\RelationshipCache;
use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Database\Queries\MySQLQuery;
use Curfle\Database\Schema\Blueprint;
use Curfle\Database\Schema\BuilderColumn;
use Curfle\Database\Schema\ForeignKeyConstraint;
use Curfle\Database\Schema\MySQLSchemaBuilder;
use Curfle\Essence\Application;
use Curfle\Support\Facades\Facade;
use Curfle\Tests\Resources\DummyClasses\DAO\Job;
use Curfle\Tests\Resources\DummyClasses\DAO\Login;
use Curfle\Tests\Resources\DummyClasses\DAO\Phone;
use Curfle\Tests\Resources\DummyClasses\DAO\Role;
use Curfle\Tests\Resources\DummyClasses\DAO\User;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
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
        $this->app->singleton("relationshipcache", function () {
            return new RelationshipCache();
        });
        Facade::setFacadeApplication($this->app);
        Container::setInstance($this->app);

        $this->builder->dropIfExists("user_role");
        $this->builder->dropIfExists("login");
        $this->builder->dropIfExists("phone");
        $this->builder->dropIfExists("user");
        $this->builder->dropIfExists("job");
        $this->builder->dropIfExists("role");

        $this->builder->create("job", function (Blueprint $table) {
            $table->id("id");
            $table->string("name");
        });

        $this->builder->create("phone", function (Blueprint $table) {
            $table->id("id");
            $table->string("number");
            $table->int("user_id")->unsigned()->nullable()->unique();
        });

        $this->builder->create("user", function (Blueprint $table) {
            $table->id("id");
            $table->string("firstname", 250);
            $table->string("lastname", 250);
            $table->string("email", 250)->nullable();
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
     * Tests the ::create() function.
     */
    public function testCreate()
    {
        User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $this->assertCount(1, User::all());
    }

    /**
     * Tests the ->save() function.
     */
    public function testSave()
    {
        $user = new User("Jane", "Doe", "jane.doe@example.dd");
        $user->store();

        $this->assertCount(1, User::all());
    }

    /**
     * Tests the ::get() function.
     */
    public function testGet()
    {
        $user = new User("Jane", "Doe");
        $user->store();

        $this->assertSame("Jane", User::get(1)->firstname);
        $this->assertNull(User::get(1)->email);
    }

    /**
     * Tests the ->update() function.
     */
    public function test()
    {
        $user = new User("Jane", "Doe");
        $user->store();

        $this->assertSame("Jane", User::get(1)->firstname);

        $user->firstname = "John";
        $user->update();

        $this->assertSame("John", User::get(1)->firstname);
    }

    /**
     * Tests the ->delete() function.
     */
    public function testDelete()
    {
        User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $this->assertCount(1, User::all());

        User::get(1)->delete();

        $this->assertEmpty(User::all());
    }

    /**
     * Tests the ->delete() function but assert that it is only soft deleted.
     */
    public function testDeleteButAssertSoftDelete()
    {
        User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        User::create([
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.dd"
        ]);

        $this->assertCount(2, User::all());

        User::get(1)->delete();

        $this->assertCount(1, User::all());

        $this->assertCount(2, User::$connector->table("user")->get());
    }

    /**
     * Tests the SQLQueryBuilder access.
     */
    public function testSQL()
    {
        User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $this->assertCount(1, User::all());

        User::where("id", 1)->delete();

        $this->assertEmpty(User::all());
    }

    /**
     * Tests one-to-one relationships
     */
    public function testOneToOne()
    {

        $user = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $phone = Phone::create([
            "number" => "+49 1234 56789",
            "user_id" => $user->id
        ]);

        $this->assertEquals($phone, User::get(1)->phone);
    }

    /**
     * Tests one-to-one relationship edits
     */
    public function testOneToOneEditing()
    {

        $jane = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $john = User::create([
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.dd"
        ]);

        $phone = Phone::create([
            "number" => "+49 1234 56789",
            "user_id" => $jane->id
        ]);

        $this->assertEquals($phone, $jane->phone);

        // give the phone to john
        $john->phone()->set($phone);
        $this->assertEquals($phone, $john->phone);

        // detach job
        $john->phone()->detach();
        $this->assertNull( $john->phone);
    }

    /**
     * Tests one-to-one lazy cache
     */
    public function testOneToOneLazy()
    {

        $jane = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $john = User::create([
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.dd"
        ]);

        $phone = Phone::create([
            "number" => "+49 1234 56789",
            "user_id" => $jane->id
        ]);

        $this->assertEquals($phone, $jane->phone()->lazy());

        // give the phone to john
        $john->phone()->set($phone);
        $this->assertEquals($phone->id, $john->phone()->lazy()->id);

        //

        // detatch object and ensure cache is still active
        $john->phone()->detach();
        $this->assertEquals($phone->id, $jane->phone()->lazy()->id);
        $this->assertEquals($phone->id, $john->phone()->lazy()->id);
        $this->assertNull( $john->phone()->lazy(true));
        $this->assertNull( $john->phone()->lazy(true));
    }


    /**
     * Tests one-to-many relationships
     */
    public function testOneToMany()
    {
        $user = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        Login::create([
            "user_id" => $user->id
        ]);

        Login::create([
            "user_id" => $user->id
        ]);

        $this->assertCount(2, $user->logins);
    }


    /**
     * Tests one-to-many relationship edits
     */
    public function testOneToManyEdit()
    {
        $user = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $loginOne = Login::create([
            "user_id" => $user->id
        ]);

        $loginTwo = Login::create([
            "user_id" => $user->id
        ]);

        $this->assertCount(2, $user->logins);

        $user->logins()->dissociate($loginOne);
        $this->assertCount(1, $user->logins);
        $this->assertEquals(null, Login::get(1)->user_id);

        $user->logins()->associate($loginOne);
        $this->assertCount(2, $user->logins);
        $this->assertEquals($user->id, Login::get(1)->user_id);
    }


    /**
     * Tests one-to-many lazy cache
     */
    public function testOneToManyLazy()
    {
        $user = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $loginOne = Login::create([
            "user_id" => $user->id
        ]);

        $loginTwo = Login::create([
            "user_id" => $user->id
        ]);

        $this->assertCount(2, $user->logins()->lazy());

        $user->logins()->dissociate($loginOne);
        $user->logins()->dissociate($loginTwo);

        $this->assertCount(2, $user->logins()->lazy());
        $this->assertCount(0, $user->logins()->lazy(true));
    }


    /**
     * Tests many-to-one relationships
     */
    public function testManyToOne()
    {
        $user = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $login = Login::create([
            "user_id" => $user->id
        ]);

        $this->assertEquals($user, $login->user);
    }


    /**
     * Tests many-to-one relationship edits
     */
    public function testManyToOneEdit()
    {
        $user = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $login = Login::create([
            "user_id" => $user->id
        ]);

        $this->assertEquals($user, $login->user);

        $login->user()->dissociate();
        $this->assertNull($login->user);

        $login->user()->associate($user);
        $this->assertEquals($user, $login->user);

    }


    /**
     * Tests many-to-one lazy cache
     */
    public function testManyToOneLazy()
    {
        $user = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $login = Login::create([
            "user_id" => $user->id
        ]);

        $this->assertEquals($user, $login->user()->lazy());
        $login->user()->dissociate();
        $this->assertEquals($user, $login->user()->lazy());
        $this->assertNull($login->user()->lazy(true));
    }


    /**
     * Tests many-to-many relationships
     */
    public function testManyToMany()
    {
        $jane = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe"
        ]);

        $john = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe"
        ]);

        $userRole = Role::create(["name" => "user"]);
        $adminRole = Role::create(["name" => "admin"]);

        $jane->roles()->attach($adminRole);
        $jane->roles()->attach($userRole);
        $john->roles()->attach($userRole);

        self::assertEquals($adminRole, $jane->roles[0]);
        self::assertEquals($userRole, $jane->roles[1]);
        self::assertEquals($userRole, $john->roles[0]);
    }


    /**
     * Tests many-to-many relationship edits
     */
    public function testManyToManyEdit()
    {
        $jane = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe"
        ]);

        $john = User::create([
            "firstname" => "Jane",
            "lastname" => "Doe"
        ]);

        $userRole = Role::create(["name" => "user"]);
        $adminRole = Role::create(["name" => "admin"]);

        $jane->roles()->attach($adminRole);
        $jane->roles()->attach($userRole);
        $john->roles()->attach($userRole);

        self::assertCount(2, $jane->roles);
        self::assertCount(1, $john->roles);

        self::assertEquals($adminRole, $jane->roles[0]);
        self::assertEquals($userRole, $jane->roles[1]);
        self::assertEquals($userRole, $john->roles[0]);

        $john->roles()->detach($userRole);
        self::assertCount(0, $john->roles);
        self::assertCount(2, $jane->roles);

        $john->roles()->attach($userRole);
        self::assertEquals($userRole, $john->roles[0]);

        $jane->roles()->detach();
        self::assertCount(0, $jane->roles);
        self::assertEquals($userRole, $john->roles[0]);

    }
}