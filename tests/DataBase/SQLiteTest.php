<?php

use Curfle\Contracts\FileSystem\FileNotFoundException;
use PHPUnit\Framework\TestCase;
use Curfle\FileSystem\FileSystem;

class SQLiteTest extends TestCase
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
     * test ::exec()
     */
    public function testExec()
    {
        $this->assertTrue(
            $this->connector->exec("
                CREATE TABLE Persons (
                    PersonID int,
                    LastName varchar(255),
                    FirstName varchar(255),
                    Address varchar(255),
                    City varchar(255) 
                );
            ")
        );
    }
}