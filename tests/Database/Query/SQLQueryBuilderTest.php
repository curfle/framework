<?php

namespace Curfle\Tests\Database\Query;

use Curfle\Database\Connectors\SQLiteConnector;
use Curfle\Essence\Application;
use Curfle\Support\Facades\DB;
use Curfle\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class SQLQueryBuilderTest extends TestCase
{

    protected function setUp(): void
    {
        // fake application
        $app = new Application();
        $app->singleton("db", function() {
            return new SQLiteConnector(DB_SQLITE_FILENAME);
        });
        Facade::setFacadeApplication($app);
    }

    /**
     * Builds simple SELECT query
     */
    public function testsimpleSelectQuery() {
        $this->assertEquals(
            DB::table("users")
                ->build(),
            "SELECT * FROM users"
        );
    }

    /**
     * tests value() and where()
     */
    public function testValueAndWhereInSelect() {
        $this->assertEquals(
            DB::table("users")
                ->where("name", "John")
                ->value("email")
                ->build(),
            "SELECT email FROM users WHERE name = 'John'"
        );
    }

    /**
     * tests multiple value() and where()
     */
    public function testMultipleValueAndWhereInSelect() {
        $this->assertEquals(
            DB::table("users")
                ->where("name", "John")
                ->value("email")
                ->value("name")
                ->value("created")
                ->build(),
            "SELECT email, name, created FROM users WHERE name = 'John'"
        );

        $this->assertEquals(
            DB::table("users")
                ->where("name", "John")
                ->value("email", "name", "created")
                ->build(),
            "SELECT email, name, created FROM users WHERE name = 'John'"
        );
    }

    /**
     * tests orderBy() and where()
     */
    public function testOrderByAndWhereInSelect() {
        $this->assertEquals(
            DB::table("users")
                ->where("email", "john@example.de")
                ->orderBy("id")
                ->build(),
            "SELECT * FROM users WHERE email = 'john@example.de' ORDER BY id"
        );
    }

    /**
     * tests orderBy() and multiple where()
     */
    public function testOrderByAndMultipleWhereInSelect() {
        $this->assertEquals(
            DB::table("users")
                ->where("email", "john@example.de")
                ->where("registered", true)
                ->where("id", 5)
                ->orderBy("id", "ASC")
                ->value("name")
                ->build(),
            "SELECT name FROM users WHERE email = 'john@example.de' AND registered = 1 AND id = 5 ORDER BY id ASC"
        );
    }

    /**
     * tests distinct()
     */
    public function testDistinctInSelect() {
        $this->assertEquals(
            DB::table("users")
                ->distinct()
                ->build(),
            "SELECT DISTINCT * FROM users"
        );
    }

    /**
     * tests groupBy()
     */
    public function testGroupByInSelect() {
        $this->assertEquals(
            DB::table("users")
                ->groupBy("registered")
                ->groupBy("created")
                ->build(),
            "SELECT * FROM users GROUP BY registered, created"
        );

        $this->assertEquals(
            DB::table("users")
                ->groupBy("registered", "created")
                ->build(),
            "SELECT * FROM users GROUP BY registered, created"
        );
    }

    /**
     * tests groupBy() with having()
     */
    public function testGroupByWithHavingInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->groupBy("registered")
                ->groupBy("created")
                ->having("id", ">=", 5)
                ->build(),
            "SELECT * FROM users GROUP BY registered, created HAVING id >= 5"
        );
    }

    /**
     * tests offset() and limit()
     */
    public function testOffsetAndLimitInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->offset(20)
                ->limit(10)
                ->build(),
            "SELECT * FROM users LIMIT 10 OFFSET 20"
        );
    }

    /**
     * tests join()
     */
    public function testJoinInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->join("payments", "payments.userId", "=", "users.id")
                ->build(),
            "SELECT * FROM users JOIN payments ON payments.userId = users.id"
        );
    }

    /**
     * tests leftJoin()
     */
    public function testLeftJoinInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->leftJoin("payments", "payments.userId", "=", "users.id")
                ->build(),
            "SELECT * FROM users LEFT JOIN payments ON payments.userId = users.id"
        );
    }

    /**
     * tests leftJoin()
     */
    public function testRightJoinInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->rightJoin("payments", "payments.userId", "=", "users.id")
                ->build(),
            "SELECT * FROM users RIGHT JOIN payments ON payments.userId = users.id"
        );
    }

    /**
     * tests leftJoin()
     */
    public function testLeftOuterJoinInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->leftOuterJoin("payments", "payments.userId", "=", "users.id")
                ->build(),
            "SELECT * FROM users LEFT OUTER JOIN payments ON payments.userId = users.id"
        );
    }

    /**
     * tests leftJoin()
     */
    public function testRightOuterJoinInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->rightOuterJoin("payments", "payments.userId", "=", "users.id")
                ->build(),
            "SELECT * FROM users RIGHT OUTER JOIN payments ON payments.userId = users.id"
        );
    }

    /**
     * tests crossJoin()
     */
    public function testCrossJoinInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->crossJoin("payments")
                ->build(),
            "SELECT * FROM users CROSS JOIN payments"
        );
    }

    /**
     * tests whereBetween()
     */
    public function testWhereBetweenInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->whereBetween("id", 5, 10)
                ->build(),
            "SELECT * FROM users WHERE id BETWEEN 5 AND 10"
        );

        $this->assertEquals(
            DB::table("users")
                ->whereBetween("id", 5, 10)
                ->whereBetween("created", 1234567, 2345678)
                ->build(),
            "SELECT * FROM users WHERE id BETWEEN 5 AND 10 AND created BETWEEN 1234567 AND 2345678"
        );
    }

    /**
     * tests whereBetween() and orWhereBetween()
     */
    public function testWhereBetweenOrWhereBetweenInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->whereBetween("id", 5, 10)
                ->orWhereBetween("created", 1234567, 2345678)
                ->build(),
            "SELECT * FROM users WHERE id BETWEEN 5 AND 10 OR created BETWEEN 1234567 AND 2345678"
        );
    }

    /**
     * tests whereNotBetween()
     */
    public function testWhereNotBetweenInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->whereNotBetween("id", 5, 10)
                ->build(),
            "SELECT * FROM users WHERE id NOT BETWEEN 5 AND 10"
        );
        $this->assertEquals(
            DB::table("users")
                ->whereNotBetween("id", 5, 10)
                ->whereNotBetween("created", 1234567, 2345678)
                ->build(),
            "SELECT * FROM users WHERE id NOT BETWEEN 5 AND 10 AND created NOT BETWEEN 1234567 AND 2345678"
        );
    }

    /**
     * tests whereNotBetween() and orWhereNotBetween()
     */
    public function testWhereNotBetweenOrWhereNotBetweenInSelect()
    {
        $this->assertEquals(
            DB::table("users")
                ->whereNotBetween("id", 5, 10)
                ->orWhereNotBetween("created", 1234567, 2345678)
                ->build(),
            "SELECT * FROM users WHERE id NOT BETWEEN 5 AND 10 OR created NOT BETWEEN 1234567 AND 2345678"
        );
    }

    /**
     * tests insert()
     */
    public function testInsert()
    {
        $this->assertEquals(
            DB::table("users")
                ->whereNotBetween("id", 5, 10)
                ->orWhereNotBetween("created", 1234567, 2345678)
                ->build(),
            "SELECT * FROM users WHERE id NOT BETWEEN 5 AND 10 OR created NOT BETWEEN 1234567 AND 2345678"
        );
    }


}