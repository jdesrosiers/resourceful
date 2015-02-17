<?php

namespace JDesrosiers\Silex\Generic\Test;

use JDesrosiers\Silex\Generic\PutResourceController;
use JDesrosiers\Silex\Resourceful;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class PutResourceControllerTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $service;
    private $client;

    public function setUp()
    {
        $this->app = new Resourceful(array("rootPath" => __DIR__));
        $this->app["debug"] = true;

        $this->service = $this->getMock("Doctrine\Common\Cache\Cache");
        $this->app->put("/foo/{id}", new PutResourceController($this->service, "/schema/foo"));
        $this->app["json-schema.schema-store"]->add("/schema/foo", $this->app["schemaService"]->fetch("/schema/foo"));

        $this->client = new Client($this->app);
    }

    public function testCreate()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->service->method("contains")
            ->with("/foo/$foo->id")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("PUT", "/foo/$foo->id", array(), array(), $headers, "{\"id\":\"$foo->id\"}");
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/foo\"", $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }

    public function testBadRequest()
    {
        $errorMessage = '[{"code":303,"dataPath":"\/illegalField","schemaPath":"\/additionalProperties","message":"Additional properties not allowed"}]';
        $this->service->method("contains")
            ->with("/foo/4ee8e29d45851")
            ->willReturn(true);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $data = '{"id":"4ee8e29d45851","illegalField":"illegal"}';
        $this->client->request("PUT", "/foo/4ee8e29d45851", array(), array(), $headers, $data);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/error\"", $response->headers->get("Content-Type"));
        $this->assertEquals(0, $content->code);
        $this->assertEquals($errorMessage, $content->message);
    }

    public function testUpdate()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->service->method("contains")
            ->with("/foo/$foo->id")
            ->willReturn(true);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("PUT", "/foo/$foo->id", array(), array(), $headers, "{\"id\":\"$foo->id\"}");
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/foo\"", $response->headers->get("Content-Type"));
        $this->assertFalse($response->headers->has("Location"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }

    public function testSaveError()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->service->method("contains")
            ->with("/foo/$foo->id")
            ->willReturn(false);

        $this->service->method("save")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("PUT", "/foo/$foo->id", array(), array(), $headers, "{\"id\":\"$foo->id\"}");
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/error\"", $response->headers->get("Content-Type"));
        $this->assertEquals(0, $content->code);
        $this->assertEquals("Failed to save resource", $content->message);
    }
}
