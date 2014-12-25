<?php

use JDesrosiers\App\Service\GenericService;
use JDesrosiers\App\Service\SessionService;

require __DIR__ . "/../../../vendor/autoload.php";

class SessionServiceTest extends PHPUnit_Framework_TestCase
{
    private $service;

    public function setUp()
    {
        $_SESSION = array();
        $this->service = new SessionService("test");
    }

    public function testStoreNewObject()
    {
        $this->assertEquals(GenericService::CREATED, $this->service->put(uniqid(), "foo"));
    }

    public function testReplaceObject()
    {
        $id = uniqid();
        $this->service->put($id, "foo");

        $this->assertEquals(GenericService::UPDATED, $this->service->put($id, "bar"));
    }

    public function testRetrieveObject()
    {
        $id = uniqid();
        $this->service->put($id, "foo");

        $this->assertEquals("foo", $this->service->get($id));
    }

    public function testRetrieveUpdatedObject()
    {
        $id = uniqid();
        $this->service->put($id, "foo");
        $this->service->put($id, "bar");

        $this->assertEquals("bar", $this->service->get($id));
    }

    public function testDeleteObject()
    {
        $id = uniqid();
        $this->service->put($id, "foo");

        $this->assertEquals(GenericService::DELETED, $this->service->delete($id));
    }

    public function testDeleteNonExistentObject()
    {
        $this->assertEquals(GenericService::NO_SUCH_ITEM, $this->service->delete(uniqid()));
    }
}
