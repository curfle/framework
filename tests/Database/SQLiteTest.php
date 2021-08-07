<?php

namespace Curfle\Tests\Database;

use Curfle\Support\Exceptions\FileSystem\FileNotFoundException;
use Curfle\Support\Exceptions\Logic\LogicException;
use PHPUnit\Framework\TestCase;

class SQLiteTest extends TestCase
{
    public \Curfle\Database\Connectors\SQLiteConnector $connector;

    public function __construct()
    {
        parent::__construct();
        $this->connector = new \Curfle\Database\Connectors\SQLiteConnector(
            __DIR__ . "/../Resources/Database/database.db"
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
     * @throws FileNotFoundException
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

    /**
     * test ::prepare()
     * @throws LogicException
     * @throws FileNotFoundException
     */
    public function testPrepareExecute()
    {
        // create table
        $this->connector->exec("
            CREATE TABLE Persons (
                PersonId int,
                LastName varchar(255),
                FirstName varchar(255),
                Address varchar(255),
                City varchar(255) 
            );
        ");

        // insert data
        $this->connector
            ->prepare("INSERT INTO Persons (PersonId, FirstName, LastName, Address, City) VALUES (?, ?, ?, ?, ?)")
            ->bind(42)
            ->bind("Jane")
            ->bind("Doe")
            ->bind("Example Street 42")
            ->bind("Example City")
            ->execute();

        // get the person
        $person = $this->connector
            ->prepare("SELECT * FROM Persons WHERE PersonId = ?")
            ->bind(42)
            ->row();

        // make assertion
        $this->assertSame(
            [
                "PersonId" => 42,
                "LastName" => "Doe",
                "FirstName" => "Jane",
                "Address" => "Example Street 42",
                "City" => "Example City",
            ],
            $person
        );
    }
}