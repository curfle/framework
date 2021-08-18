<?php

namespace Curfle\Tests\FileSystem;

use Curfle\Support\Exceptions\FileSystem\FileNotFoundException;
use PHPUnit\Framework\TestCase;
use Curfle\FileSystem\FileSystem;

class FileSystemTest extends TestCase
{
    private FileSystem $fileSystem;

    public function __construct()
    {
        parent::__construct();
        $this->fileSystem = new FileSystem();
    }
    /**
     * test ::get()
     * @throws FileNotFoundException
     */
    public function testGet()
    {
        $this->assertEquals(
            file_get_contents(__DIR__ . "/FileSystemTest.php"),
            $this->fileSystem->get(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::exists()
     */
    public function testExists()
    {
        $this->assertTrue(
            $this->fileSystem->exists(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::name()
     */
    public function testName()
    {
        $this->assertSame(
            "FileSystemTest",
            $this->fileSystem->name(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::testExtension()
     */
    public function testExtension()
    {
        $this->assertSame(
            "php",
            $this->fileSystem->extension(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::testSize()
     */
    public function testSize()
    {
        $this->assertGreaterThan(
            0,
            $this->fileSystem->size(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::testBasename()
     */
    public function testBasename()
    {
        $this->assertSame(
            "FileSystem",
            $this->fileSystem->basename(__DIR__)
        );
    }

    /**
     * test ::testIsFile()
     */
    public function testIsFile()
    {
        $this->assertTrue(
            $this->fileSystem->isFile(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::isDirectory()
     */
    public function testIsDirectory()
    {
        $this->assertTrue(
            $this->fileSystem->isDirectory(__DIR__)
        );
    }

    /**
     * test ::mimeType()
     */
    public function testMimeType()
    {
        $this->assertSame(
            "text/x-php",
            $this->fileSystem->mimeType(__DIR__ . "/FileSystemTest.php")
        );
    }

    /**
     * test ::contentType()
     */
    public function testContentType()
    {
        $this->assertSame(
            "application/x-httpd-php",
            $this->fileSystem->contentType(__DIR__ . "/FileSystemTest.php")
        );
    }
}