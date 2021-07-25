<?php

use Curfle\Contracts\FileSystem\FileNotFoundException;
use PHPUnit\Framework\TestCase;
use Curfle\FileSystem\FileSystem;

class FileSystemTest extends TestCase
{
    /**
     * test ::get()
     * @throws FileNotFoundException
     */
    public function testGet()
    {
        $this->assertEquals(
            file_get_contents(__DIR__ . "/FileSystemTest.php"),
            FileSystem::get(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::exists()
     */
    public function testExists()
    {
        $this->assertTrue(
            FileSystem::exists(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::name()
     */
    public function testName()
    {
        $this->assertSame(
            "FileSystemTest",
            FileSystem::name(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::testExtension()
     */
    public function testExtension()
    {
        $this->assertSame(
            "php",
            FileSystem::extension(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::testSize()
     */
    public function testSize()
    {
        $this->assertGreaterThan(
            0,
            FileSystem::size(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::testBasename()
     */
    public function testBasename()
    {
        $this->assertSame(
            "FileSystem",
            FileSystem::basename(__DIR__)
        );
    }

    /**
     * test ::testIsFile()
     */
    public function testIsFile()
    {
        $this->assertTrue(
            FileSystem::isFile(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::isDirectory()
     */
    public function testIsDirectory()
    {
        $this->assertTrue(
            FileSystem::isDirectory(__DIR__)
        );
    }

    /**
     * test ::isDirectory()
     */
    public function testMimeType()
    {
        $this->assertSame(
            "text/x-php",
            FileSystem::mimeType(__DIR__ . "/FileSystemTest.php")
        );
    }
}