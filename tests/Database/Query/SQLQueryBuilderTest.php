<?php

namespace Curfle\Tests\Database\Queries;

use Curfle\Database\Connectors\SQLiteConnector;
use Curfle\Database\Queries\Builders\SQLiteQueryBuilder;
use Curfle\Database\Schema\Blueprint;
use Curfle\Essence\Application;
use Curfle\Support\Facades\DB;
use Curfle\Support\Facades\Facade;
use Curfle\Support\Facades\Schema;
use PHPUnit\Framework\TestCase;

class SQLQueryBuilderTest extends TestCase
{

    protected function setUp(): void
    {
        // fake application
        $app = new Application();
        $app->singleton("db", function () {
            return new SQLiteConnector(DB_SQLITE_FILENAME);
        });
        Facade::setFacadeApplication($app);


        DB::execute("DROP TABLE IF EXISTS users");
        DB::execute("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name varchar(100))");
    }

    protected function tearDown(): void
    {
        DB::execute("DROP TABLE IF EXISTS users");
    }

    /**
     * Builds simple SELECT query
     */
    public function testsimpleSelectQuery()
    {
        $this->assertEquals(
            "SELECT * FROM users",
            DB::table("users")
                ->build()->getQuery()
        );
    }

    /**
     * tests value() and where()
     */
    public function testValueAndWhereInSelect()
    {
        $this->assertEquals(
            "SELECT email FROM users WHERE name=?",
            DB::table("users")
                ->where("name", "John")
                ->select("email")
                ->build()->getQuery()
        );
    }

    /**
     * tests multiple value() and where()
     */
    public function testMultipleValueAndWhereInSelect()
    {
        $this->assertEquals(
            "SELECT email, name, created FROM users WHERE name=?",
            DB::table("users")
                ->where("name", "John")
                ->select("email")
                ->select("name")
                ->select("created")
                ->build()->getQuery()
        );

        $this->assertEquals(
            "SELECT email, name, created FROM users WHERE name=?",
            DB::table("users")
                ->where("name", "John")
                ->select("email")
                ->select("name")
                ->select("created")
                ->build()->getQuery()
        );
    }

    /**
     * tests orderBy() and where()
     */
    public function testOrderByAndWhereInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users WHERE email=? ORDER BY id",
            DB::table("users")
                ->where("email", "john@example.de")
                ->orderBy("id")
                ->build()->getQuery()
        );
    }

    /**
     * tests orderBy() and multiple where()
     */
    public function testOrderByAndMultipleWhereInSelect()
    {
        $this->assertEquals(
            "SELECT name FROM users WHERE email=? AND registered=? AND id=? ORDER BY id ASC",
            DB::table("users")
                ->where("email", "john@example.de")
                ->select("name")
                ->where("registered", true)
                ->where("id", 5)
                ->orderBy("id", "ASC")
                ->build()->getQuery()
        );
    }

    /**
     * tests distinct()
     */
    public function testDistinctInSelect()
    {
        $this->assertEquals(
            "SELECT DISTINCT * FROM users",
            DB::table("users")
                ->distinct()
                ->build()->getQuery()
        );
    }

    /**
     * tests groupBy()
     */
    public function testGroupByInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users GROUP BY registered, created",
            DB::table("users")
                ->groupBy("registered")
                ->groupBy("created")
                ->build()->getQuery()
        );

        $this->assertEquals(
            "SELECT * FROM users GROUP BY registered, created",
            DB::table("users")
                ->groupBy("registered", "created")
                ->build()->getQuery()
        );
    }

    /**
     * tests groupBy() with having()
     */
    public function testGroupByWithHavingInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users GROUP BY registered, created HAVING id>=?",
            DB::table("users")
                ->groupBy("registered")
                ->groupBy("created")
                ->having("id", ">=", 5)
                ->build()->getQuery()
        );
    }

    /**
     * tests offset() and limit()
     */
    public function testOffsetAndLimitInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users LIMIT 10 OFFSET 20",
            DB::table("users")
                ->offset(20)
                ->limit(10)
                ->build()->getQuery()
        );
    }

    /**
     * tests join()
     */
    public function testJoinInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users JOIN payments ON payments.userId = users.id",
            DB::table("users")
                ->join("payments", "payments.userId", "=", "users.id")
                ->build()->getQuery()
        );
    }

    /**
     * tests leftJoin()
     */
    public function testLeftJoinInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users LEFT JOIN payments ON payments.userId = users.id",
            DB::table("users")
                ->leftJoin("payments", "payments.userId", "=", "users.id")
                ->build()->getQuery()
        );
    }

    /**
     * tests leftJoin()
     */
    public function testRightJoinInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users RIGHT JOIN payments ON payments.userId = users.id",
            DB::table("users")
                ->rightJoin("payments", "payments.userId", "=", "users.id")
                ->build()->getQuery()
        );
    }

    /**
     * tests leftJoin()
     */
    public function testLeftOuterJoinInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users LEFT OUTER JOIN payments ON payments.userId = users.id",
            DB::table("users")
                ->leftOuterJoin("payments", "payments.userId", "=", "users.id")
                ->build()->getQuery(),
        );
    }

    /**
     * tests leftJoin()
     */
    public function testRightOuterJoinInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users RIGHT OUTER JOIN payments ON payments.userId = users.id",
            DB::table("users")
                ->rightOuterJoin("payments", "payments.userId", "=", "users.id")
                ->build()->getQuery()
        );
    }

    /**
     * tests crossJoin()
     */
    public function testCrossJoinInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users CROSS JOIN payments",
            DB::table("users")
                ->crossJoin("payments")
                ->build()->getQuery()
        );
    }

    /**
     * tests insert()
     */
    public function testInsert()
    {
        $this->assertEquals(
            "INSERT INTO users (id, name, email) VALUES (?, ?, ?)",
            (new SQLiteQueryBuilder())->table("users")
                ->insert([
                    "id" => 2,
                    "name" => "Jane",
                    "email" => "jane@doe.dd"
                ])->build()->getQuery()
        );
    }

    /**
     * tests min()
     */
    public function testMin()
    {
        // insert dummy user
        for ($i = 0; $i < 2; $i++)
            DB::table("users")->insert(["name" => "User $i"]);

        $this->assertEquals(
            1,
            DB::table("users")->min("id")
        );
    }

    /**
     * tests max()
     */
    public function testMax()
    {
        // insert dummy user
        for ($i = 0; $i < 2; $i++)
            DB::table("users")->insert(["name" => "User $i"]);

        $this->assertEquals(
            2,
            DB::table("users")->max("id")
        );
    }

    /**
     * tests avg()
     */
    public function testAvg()
    {
        // insert dummy user
        for ($i = 0; $i < 3; $i++)
            DB::table("users")->insert(["name" => "User $i"]);

        $this->assertEquals(
            2,
            DB::table("users")->avg("id")
        );
    }

    /**
     * tests count()
     */
    public function testCount()
    {
        // insert dummy user
        for ($i = 0; $i < 3; $i++)
            DB::table("users")->insert(["name" => $i <= 1 ? "User $i" : null]);

        $this->assertEquals(
            3,
            DB::table("users")->count()
        );

        $this->assertEquals(
            2,
            DB::table("users")->count("name")
        );
    }

    /**
     * tests count()
     */
    public function testInsertOrUpdate()
    {
        // insert dummy users
        for ($i = 0; $i < 3; $i++)
            DB::table("users")->insert(["name" => "User($i)"]);

        $this->assertEquals(
            3,
            DB::table("users")->count()
        );

        // insert or update operation
        DB::table("users")->insertOrUpdate(["id" => 3, "name" => "User(4)"]);

        $this->assertSame("User(4)", DB::table("users")->where("id", 3)->first()["name"]);
    }
}