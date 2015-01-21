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
        $this->assertEquals(GenericService::CREATED, $this->service->put(uniqid(), "foo"));
    }

    public function testReplaceObject()
    {
        $id = uniqid();
        $this->service->put($id, "foo");

        $this->assertEquals(GenericService::OK, $this->service->put($id, "bar"));
    }

    public function testRetrieveObject()
    {
        $id = uniqid();
        $this->service->put($id, "foo");

        list($status, $object) = $this->service->get($id);
        $this->assertEquals(GenericService::OK, $status);
        $this->assertEquals("foo", $object);
    }

    public function testRetrieveUpdatedObject()
    {
        $id = uniqid();
        $this->service->put($id, "foo");
        $this->service->put($id, "bar");

        list($status, $object) = $this->service->get($id);
        $this->assertEquals(GenericService::OK, $status);
        $this->assertEquals("bar", $object);
    }

    public function testRetrieveNonexistentObject()
    {
        list($status) = $this->service->get("non-existent-id");
        $this->assertEquals(GenericService::NOT_FOUND, $status);
    }

    public function testDeleteObject()
    {
        $id = uniqid();
        $this->service->put($id, "foo");

        $this->assertEquals(GenericService::OK, $this->service->delete($id));
    }

    public function testDeleteNonExistentObject()
    {
        $this->assertEquals(GenericService::NOT_FOUND, $this->service->delete(uniqid()));
    }
}
