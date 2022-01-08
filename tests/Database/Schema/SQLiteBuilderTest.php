<?php

namespace Curfle\Tests\Database\Schema;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Connectors\SQLiteConnector;
use Curfle\Database\Schema\Blueprint;
use Curfle\Database\Schema\ForeignKeyConstraint;
use Curfle\Database\Schema\SQLiteSchemaBuilder;
use PHPUnit\Framework\TestCase;

class SQLiteBuilderTest extends TestCase
{
    private SQLConnectorInterface $connector;
    private SQLiteSchemaBuilder $builder;

    protected function setUp(): void
    {
        $this->builder->dropIfExists("job");
        $this->builder->dropIfExists("place");
        $this->builder->dropIfExists("employe");
        $this->builder->dropIfExists("user");
    }

    protected function tearDown(): void
    {
        $this->builder->dropIfExists("job");
        $this->builder->dropIfExists("place");
        $this->builder->dropIfExists("employe");
        $this->builder->dropIfExists("user");
    }

    public function __construct()
    {
        parent::__construct();
        $this->connector = new SQLiteConnector(DB_SQLITE_FILENAME);
        $this->builder = new SQLiteSchemaBuilder($this->connector);
    }

    /**
     * test ->create()
     */
    public function testCreate()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function(Blueprint $table){
                $table->id("id");
                $table->string("firstname", 250);
                $table->string("lastname", 250, true);
                $table->tinyInt("registered");
                $table->int("age");
                $table->bigInt("numberOfLogins");
                $table->float("secondsFor100m");
                $table->date("birthday");
                $table->datetime("lastLogin");
                $table->timestamp("created");
            })
        );
        $this->builder->dropIfExists("user");
    }

    /**
     * test ->create()
     */
    public function testCreateWithForeignKey()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function(Blueprint $table){
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertSame(
            $this->builder,
            $this->builder->create("job", function(Blueprint $table){
                $table->id("id");
                $table->string("name");
                $table->int("user_id")->unsigned();
                $table->foreign("user_id")
                    ->references("id")
                    ->on("user")
                    ->onDelete("cascade");
            })
        );
    }

    /**
     * test ->table()
     */
    public function testTable()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function(Blueprint $table){
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertSame(
            $this->builder,
            $this->builder->table("user", function(Blueprint $table){
                $table->datetime("birthday")->nullable();
            })
        );
    }

    /**
     * test ->table()
     */
    public function testTableWithForeignKey()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function(Blueprint $table){
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertSame(
            $this->builder,
            $this->builder->create("job", function(Blueprint $table){
                $table->id("id");
                $table->string("name");
                $table->int("user_id")->unsigned();
                $table->foreign("user_id")
                    ->references("id")
                    ->on("user")
                    ->onDelete(ForeignKeyConstraint::CASCADE);
            })
        );

        self::assertSame(
            $this->builder,
            $this->builder->create("place", function(Blueprint $table){
                $table->id("id");
                $table->string("adress");
            })
        );
    }

    /**
     * test ->hasTable()
     */
    public function testHasTable()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertTrue($this->builder->hasTable("user"));
        self::assertFalse($this->builder->hasTable("unkownTable"));
    }

    /**
     * test ->drop()
     */
    public function testDropTable()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertTrue($this->builder->hasTable("user"));

        $this->builder->drop("user");

        self::assertFalse($this->builder->hasTable("user"));
    }

    /**
     * test ->rename()
     */
    public function testRenameTable()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertTrue($this->builder->hasTable("user"));

        $this->builder->rename("user", "employe");

        self::assertFalse($this->builder->hasTable("user"));
        self::assertTrue($this->builder->hasTable("employe"));
    }

    /**
     * test ->dropIfExists()
     */
    public function testDropIfExistsTable()
    {
        $this->builder->dropIfExists("user");

        self::assertSame(
            $this->builder,
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertTrue($this->builder->hasTable("user"));

        $this->builder->dropIfExists("user");

        self::assertFalse($this->builder->hasTable("user"));
    }

    /**
     * test ->hasColumn()
     */
    public function testHasColumn()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertTrue($this->builder->hasColumn("user", "firstname"));
    }

    /**
     * test ->softDeletes()
     */
    public function testEnum()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->enum("type", ["USER", "ADMIN", "OWNER"]);
                $table->softDeletes();
            })
        );
    }

    /**
     * test ->time()
     */
    public function testTime()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->time("birthtime");
            })
        );
    }
}