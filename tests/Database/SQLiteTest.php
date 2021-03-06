<?php

namespace Curfle\Tests\Database;

use Curfle\Support\Exceptions\FileSystem\FileNotFoundException;
use Curfle\Support\Exceptions\Logic\LogicException;
use Curfle\Database\Connectors\SQLiteConnector;
use PHPUnit\Framework\TestCase;

class SQLiteTest extends TestCase
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
     * test ::exec()
     * @throws FileNotFoundException
     */
    public function testExec()
    {
        $this->assertTrue(
            $this->connector->execute("
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
        $this->connector->execute("
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
            ->bind([42, "Jane", "Doe", "Example Street 42", "Example City"])
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