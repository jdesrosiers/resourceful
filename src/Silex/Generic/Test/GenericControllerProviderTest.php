<?php

namespace JDesrosiers\Silex\Generic\Test;

use JDesrosiers\App\Service\GenericService;
use JDesrosiers\Silex\Generic\GenericControllerProvider;
use JDesrosiers\Silex\MyApplication;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

require __DIR__ . "/../../../../vendor/autoload.php";

class GenericControllerProviderTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $service;
    private $client;

    public function setUp()
    {
        $this->app = new MyApplication();
        $this->app["debug"] = true;
        $this->app["rootPath"] = __DIR__;

        $this->service = $this->getMock("JDesrosiers\App\Service\GenericService");

        $this->app->mount("/foo", new GenericControllerProvider("foo", $this->service));

        $this->client = new Client($this->app);
    }

    public function testGet()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->service->method("get")
            ->with("4ee8e29d45851")
            ->willReturn($foo);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/json; profile=/schema/foo", $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString('{"id":"4ee8e29d45851"}', $response->getContent());
    }

    public function testGetNotFound()
    {
        $this->service->method("get")
            ->with("4ee8e29d45851")
            ->willReturn(null);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testCreate()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->app["genericService.uniqid"] = $foo->id;
        $this->service->method("put")
            ->with($foo->id, $foo)
            ->willReturn(GenericService::CREATED);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("POST", "/foo/", array(), array(), $headers, '{}');
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals("application/json; profile=/schema/foo", $response->headers->get("Content-Type"));
        $this->assertEquals("/foo/$foo->id", $response->headers->get("Location"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }

    public function testBadCreateRequest()
    {
        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("POST", "/foo/", array(), array(), $headers, '{"illegalField":"illegal"}');
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testPut()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->app["genericService.uniqid"] = $foo->id;
        $this->service->method("put")
            ->with($foo->id, $foo)
            ->willReturn(GenericService::CREATED);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("PUT", "/foo/$foo->id", array(), array(), $headers, "{\"id\":\"$foo->id\"}");
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals("application/json; profile=/schema/foo", $response->headers->get("Content-Type"));
        $this->assertEquals("/foo/$foo->id", $response->headers->get("Location"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }

    public function testDelete()
    {
        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("DELETE", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEquals("", $response->getContent());
    }
}
