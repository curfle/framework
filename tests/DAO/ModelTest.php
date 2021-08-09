<?php

namespace Curfle\Tests\DAO;


use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\DAO\Model;
use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Database\Schema\Blueprint;
use Curfle\Database\Schema\MySQLSchemaBuilder;
use Curfle\Support\Facades\DB;
use Curfle\Tests\Resources\DummyClasses\User;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    private SQLConnectorInterface $connector;
    private MySQLSchemaBuilder $builder;

    public function __construct()
    {
        parent::__construct();
        $this->connector = new MySQLConnector(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $this->builder = new MySQLSchemaBuilder($this->connector);
    }

    protected function setUp(): void
    {
        $this->builder->dropIfExists("user");
        $this->builder->create("user", function(Blueprint $table){
            $table->id("id");
            $table->string("firstname", 250);
            $table->string("lastname", 250);
            $table->string("email", 250)->nullable();
            $table->timestamp("created")->useCurrent()->useCurrentOnUpdate();
        });
        Model::$connector = $this->connector;
    }

    protected function tearDown(): void
    {
        $this->builder->dropIfExists("user");
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
        $this->assertNull( User::get(1)->email);
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
     * Tests the ::sql() function.
     */
    public function testSQL()
    {
        User::create([
            "firstname" => "Jane",
            "lastname" => "Doe",
            "email" => "jane.doe@example.dd"
        ]);

        $this->assertCount(1, User::all());

        User::sql()->where("id", 1)->delete();

        $this->assertEmpty(User::all());
    }
}