<?php

namespace JDesrosiers\App\Service\Test;

use JDesrosiers\App\Service\FileService;
use JDesrosiers\App\Service\GenericService;
use Symfony\Component\Filesystem\Filesystem;

require __DIR__ . "/../../../../vendor/autoload.php";

class FileServiceTest extends \PHPUnit_Framework_TestCase
{
    private $service;
    private $testDir;

    public function setUp()
    {
        $this->testDir = __DIR__ . "/test";

        $this->cleanUp();
        $this->service = new FileService($this->testDir);
    }

    public function tearDown()
    {
        $this->cleanUp();
    }

    private function cleanUp()
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($this->testDir)) {
            $filesystem->remove($this->testDir);
        }
    }

    public function testStoreNewObject()
    {
        $this->service->save("foo", "bar");

        $this->assertEquals("bar", $this->service->fetch("foo"));
    }

    public function testReplaceObject()
    {
        $this->service->save("foo", "foo");
        $this->service->save("foo", "bar");

        $this->assertEquals("bar", $this->service->fetch("foo"));
    }

    public function testRetrieveNonexistentObject()
    {
        $this->assertFalse($this->service->fetch("foo"));
    }

    public function testDeleteObject()
    {
        $this->service->save("foo", "bar");
        $this->service->delete("foo");

        $this->assertFalse($this->service->fetch("foo"));
    }

    public function testDeleteNonExistentObject()
    {
        $this->service->delete("foo");
    }
}
