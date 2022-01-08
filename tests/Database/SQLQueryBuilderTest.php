<?php

namespace Curfle\Tests\Database;

use Curfle\Database\Connectors\SQLiteConnector;
use Curfle\Database\Queries\Builders\MySQLQueryBuilder;
use PHPUnit\Framework\TestCase;

class SQLQueryBuilderTest extends TestCase
{

    public SQLiteConnector $connector;

    public function __construct()
    {
        parent::__construct();
        $this->connector = new SQLiteConnector(DB_SQLITE_FILENAME);
    }

    protected function setUp(): void
    {
        $this->connector->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->connector->rollbackTransaction();
    }

    /**
     * Builds simple SELECT query
     */
    public function testsimpleSelectQuery()
    {
        $this->assertEquals(
            "SELECT * FROM users",
            $this->connector->table("users")
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
            $this->connector->table("users")
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
            $this->connector->table("users")
                ->where("name", "John")
                ->select("email")
                ->select("name")
                ->select("created")
                ->build()->getQuery()
        );

        $this->assertEquals(
            "SELECT email, name, created FROM users WHERE name=?",
            $this->connector->table("users")
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
            $this->connector->table("users")
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
            "SELECT name FROM users WHERE email=? AND registered=? AND id=? ORDER BY id DESC",
            $this->connector->table("users")
                ->where("email", "john@example.de")
                ->where("registered", true)
                ->where("id", 5)
                ->orderBy("id", "DESC")
                ->select("name")
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
            $this->connector->table("users")
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
            $this->connector->table("users")
                ->groupBy("registered")
                ->groupBy("created")
                ->build()->getQuery()
        );

        $this->assertEquals(
            "SELECT * FROM users GROUP BY registered, created",
            $this->connector->table("users")
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
            $this->connector->table("users")
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
            $this->connector->table("users")
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
            $this->connector->table("users")
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
            $this->connector->table("users")
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
            $this->connector->table("users")
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
            $this->connector->table("users")
                ->leftOuterJoin("payments", "payments.userId", "=", "users.id")
                ->build()->getQuery()
        );
    }

    /**
     * tests leftJoin()
     */
    public function testRightOuterJoinInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users RIGHT OUTER JOIN payments ON payments.userId = users.id",
            $this->connector->table("users")
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
            $this->connector->table("users")
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
            (new MySQLQueryBuilder())->table("users")
                ->insert([
                    "id" => 2,
                    "name" => "Jane",
                    "email" => "jane@doe.dd"
                ])->build()->getQuery()
        );
    }


}