<?php

namespace Curfle\Tests\Database\Schema;

use Curfle\Agreements\Database\Connectors\SQLConnectorInterface;
use Curfle\Database\Connectors\MySQLConnector;
use Curfle\Database\Schema\Blueprint;
use Curfle\Database\Schema\ForeignKeyConstraint;
use Curfle\Database\Schema\MySQLSchemaBuilder;
use Curfle\Support\Exceptions\Database\ConnectionFailedException;
use Curfle\Support\Exceptions\FileSystem\FileNotFoundException;
use Curfle\Support\Exceptions\Logic\LogicException;
use Curfle\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class MySQLBuilderTest extends TestCase
{
    private SQLConnectorInterface $connector;
    private MySQLSchemaBuilder $builder;

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
        $this->connector = new MySQLConnector(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        $this->builder = new MySQLSchemaBuilder($this->connector);
    }

    /**
     * test ->create()
     */
    public function testCreate()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function (Blueprint $table) {
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
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertSame(
            $this->builder,
            $this->builder->create("job", function (Blueprint $table) {
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
     * test ->default()
     */
    public function testCreateWithDefaults()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->string("firstname", 250)->default("Jane", false);
                $table->string("lastname", 250)->default("substring(MD5(RAND()),1,20)", false);
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
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertSame(
            $this->builder,
            $this->builder->table("user", function (Blueprint $table) {
                $table->string("firstname", 250)->change();
                $table->datetime("birthday");
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
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->string("firstname");
                $table->string("lastname");
            })
        );

        self::assertSame(
            $this->builder,
            $this->builder->create("job", function (Blueprint $table) {
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
            $this->builder->create("place", function (Blueprint $table) {
                $table->id("id");
                $table->string("adress");
            })
        );

        self::assertSame(
            $this->builder,
            $this->builder->table("job", function (Blueprint $table) {
                $table->dropForeign("FK_user_job");
                $table->dropColumn("user_id");
            })
        );

        self::assertSame(
            $this->builder,
            $this->builder->table("place", function (Blueprint $table) {
                $table->int("userId")->unsigned();
                $table->foreign("userId")
                    ->references("id")
                    ->on("user")
                    ->onUpdate(ForeignKeyConstraint::NO_ACTION);
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
     * test ->dropColumn()
     */
    public function testDropColumn()
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

        $this->builder->dropColumn("user", "firstname");

        self::assertFalse($this->builder->hasColumn("user", "firstname"));
    }

    /**
     * test ->softDeletes()
     */
    public function testSoftDeletes()
    {
        self::assertSame(
            $this->builder,
            $this->builder->create("user", function (Blueprint $table) {
                $table->id("id");
                $table->softDeletes();
            })
        );
    }

    /**
     * test ->enum()
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
}