<?php

namespace JDesrosiers\Silex\Controller\Test;

use JDesrosiers\Doctrine\Cache\FileCache;
use JDesrosiers\Silex\Controller\CreateResourceController;
use JDesrosiers\Silex\Error\JsonErrorHandler;
use JDesrosiers\Silex\Resourceful;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class CreateResourceControllerTest extends PHPUnit_Framework_TestCase
{
    private $app;
    private $service;
    private $client;

    public function setUp()
    {
        $this->app = new Resourceful();
        $this->app["debug"] = true;
        $this->app["schemaService"] = new FileCache(__DIR__);

        $this->service = $this->getMock("Doctrine\Common\Cache\Cache");
        $this->app->get("/foo/{id}")->bind("/schema/foo");
        $this->app->post("/foo/", new CreateResourceController($this->service, "/schema/foo"));
        $this->app["json-schema.schema-store"]->add("/schema/foo", $this->app["schemaService"]->fetch("/schema/foo"));

        $this->app->error(new JsonErrorHandler(true));

        $this->client = new Client($this->app);
    }

    public function testCreate()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->app["uniqid"] = $foo->id;

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("POST", "/foo/", array(), array(), $headers, "{}");
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));
        $this->assertEquals("/foo/$foo->id", $response->headers->get("Location"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }

    public function testBadRequest()
    {
        $errorMessage = '[{"code":303,"dataPath":"\/illegalField","schemaPath":"\/additionalProperties","message":"Additional properties not allowed"}]';

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("POST", "/foo/", array(), array(), $headers, '{"illegalField":"illegal"}');
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));
        $this->assertEquals(0, $content->code);
        $this->assertEquals($errorMessage, $content->message);
    }

    public function testSaveError()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->service->method("save")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("POST", "/foo/", array(), array(), $headers, "{}");
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));
        $this->assertEquals(0, $content->code);
        $this->assertEquals("Failed to save resource", $content->message);
    }
}
