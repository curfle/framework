<?php

use PHPUnit\Framework\TestCase;
use Curfle\Database\Connectors\SQLiteConnector;

class SQLQueryBuilderTest extends TestCase
{

    public \Curfle\Database\Connectors\SQLiteConnector $connector;

    public function __construct()
    {
        parent::__construct();
        $this->connector = new \Curfle\Database\Connectors\SQLiteConnector(
            __DIR__ . "/../_resources/database.db"
        );
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
    public function testsimpleSelectQuery() {
        $this->assertEquals(
            "SELECT * FROM users",
            $this->connector->table("users")
                ->build()
        );
    }

    /**
     * tests value() and where()
     */
    public function testValueAndWhereInSelect() {
        $this->assertEquals(
            "SELECT email FROM users WHERE name = 'John'",
            $this->connector->table("users")
                ->where("name", "John")
                ->value("email")
                ->build()
        );
    }

    /**
     * tests multiple value() and where()
     */
    public function testMultipleValueAndWhereInSelect() {
        $this->assertEquals(
            "SELECT email, name, created FROM users WHERE name = 'John'",
            $this->connector->table("users")
                ->where("name", "John")
                ->value("email")
                ->value("name")
                ->value("created")
                ->build()
        );

        $this->assertEquals(
            "SELECT email, name, created FROM users WHERE name = 'John'",
            $this->connector->table("users")
                ->where("name", "John")
                ->value("email", "name", "created")
                ->build()
        );
    }

    /**
     * tests orderBy() and where()
     */
    public function testOrderByAndWhereInSelect() {
        $this->assertEquals(
            "SELECT * FROM users WHERE email = 'john@example.de' ORDER BY id",
            $this->connector->table("users")
                ->where("email", "john@example.de")
                ->orderBy("id")
                ->build()
        );
    }

    /**
     * tests orderBy() and multiple where()
     */
    public function testOrderByAndMultipleWhereInSelect() {
        $this->assertEquals(
            "SELECT name FROM users WHERE email = 'john@example.de' AND registered = 1 AND id = 5 ORDER BY id DESC",
            $this->connector->table("users")
                ->where("email", "john@example.de")
                ->where("registered", true)
                ->where("id", 5)
                ->orderBy("id", "DESC")
                ->value("name")
                ->build()
        );
    }

    /**
     * tests distinct()
     */
    public function testDistinctInSelect() {
        $this->assertEquals(
            "SELECT DISTINCT * FROM users",
            $this->connector->table("users")
                ->distinct()
                ->build()
        );
    }

    /**
     * tests groupBy()
     */
    public function testGroupByInSelect() {
        $this->assertEquals(
            "SELECT * FROM users GROUP BY registered, created",
            $this->connector->table("users")
                ->groupBy("registered")
                ->groupBy("created")
                ->build()
        );

        $this->assertEquals(
            "SELECT * FROM users GROUP BY registered, created",
            $this->connector->table("users")
                ->groupBy("registered", "created")
                ->build()
        );
    }

    /**
     * tests groupBy() with having()
     */
    public function testGroupByWithHavingInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users GROUP BY registered, created HAVING id >= 5",
            $this->connector->table("users")
                ->groupBy("registered")
                ->groupBy("created")
                ->having("id", ">=", 5)
                ->build()
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
                ->build()
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
                ->build()
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
                ->build()
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
                ->build()
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
                ->build()
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
                ->build()
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
                ->build()
        );
    }

    /**
     * tests whereBetween()
     */
    public function testWhereBetweenInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users WHERE id BETWEEN 5 AND 10",
            $this->connector->table("users")
                ->whereBetween("id", 5, 10)
                ->build()
        );

        $this->assertEquals(
            "SELECT * FROM users WHERE id BETWEEN 5 AND 10 AND created BETWEEN 1234567 AND 2345678",
            $this->connector->table("users")
                ->whereBetween("id", 5, 10)
                ->whereBetween("created", 1234567, 2345678)
                ->build()
        );
    }

    /**
     * tests whereBetween() and orWhereBetween()
     */
    public function testWhereBetweenOrWhereBetweenInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users WHERE id BETWEEN 5 AND 10 OR created BETWEEN 1234567 AND 2345678",
            $this->connector->table("users")
                ->whereBetween("id", 5, 10)
                ->orWhereBetween("created", 1234567, 2345678)
                ->build()
        );
    }

    /**
     * tests whereNotBetween()
     */
    public function testWhereNotBetweenInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users WHERE id NOT BETWEEN 5 AND 10",
            $this->connector->table("users")
                ->whereNotBetween("id", 5, 10)
                ->build()
        );
        $this->assertEquals(
            "SELECT * FROM users WHERE id NOT BETWEEN 5 AND 10 AND created NOT BETWEEN 1234567 AND 2345678",
            $this->connector->table("users")
                ->whereNotBetween("id", 5, 10)
                ->whereNotBetween("created", 1234567, 2345678)
                ->build()
        );
    }

    /**
     * tests whereNotBetween() and orWhereNotBetween()
     */
    public function testWhereNotBetweenOrWhereNotBetweenInSelect()
    {
        $this->assertEquals(
            "SELECT * FROM users WHERE id NOT BETWEEN 5 AND 10 OR created NOT BETWEEN 1234567 AND 2345678",
            $this->connector->table("users")
                ->whereNotBetween("id", 5, 10)
                ->orWhereNotBetween("created", 1234567, 2345678)
                ->build()
        );
    }

    /**
     * tests insert()
     */
    public function testInsert()
    {
        $this->assertEquals(
            "SELECT * FROM users WHERE id NOT BETWEEN 5 AND 10 OR created NOT BETWEEN 1234567 AND 2345678",
            $this->connector->table("users")
                ->whereNotBetween("id", 5, 10)
                ->orWhereNotBetween("created", 1234567, 2345678)
                ->build()
        );
    }


}