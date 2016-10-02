<?php

namespace JDesrosiers\Resourceful\Controller\Test;

use JDesrosiers\Resourceful\Controller\PutResourceController;
use JDesrosiers\Resourceful\FileCache\FileCache;
use JDesrosiers\Silex\Provider\JsonSchemaServiceProvider;
use PHPUnit_Framework_TestCase;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class PutResourceControllerTest extends PHPUnit_Framework_TestCase
{
    private $app;
    private $service;
    private $client;

    public function setUp()
    {
        $this->app = new Application();
        $this->app["debug"] = true;
        $this->app->register(new JsonSchemaServiceProvider());

        $this->app["schemaService"] = new FileCache(__DIR__);

        $this->service = $this->getMockBuilder("Doctrine\Common\Cache\Cache")->getMock();
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
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }

    public function testBadRequest()
    {
        $this->service->method("contains")
            ->with("/foo/4ee8e29d45851")
            ->willReturn(true);

        $this->app->error(function (\Exception $e, $code) {
            $errorMessage = '[{"code":303,"dataPath":"\/illegalField","schemaPath":"\/additionalProperties","message":"Additional properties not allowed"}]';
            $this->assertEquals($errorMessage, $e->getMessage());
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $data = '{"id":"4ee8e29d45851","illegalField":"illegal"}';
        $this->client->request("PUT", "/foo/4ee8e29d45851", array(), array(), $headers, $data);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
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
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));
        $this->assertFalse($response->headers->has("Location"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }

    public function testSaveError()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->service->method("save")
            ->willReturn(false);

        $this->app->error(function (\Exception $e, $code) {
            $this->assertEquals("Failed to save resource", $e->getMessage());
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("PUT", "/foo/$foo->id", array(), array(), $headers, "{\"id\":\"$foo->id\"}");
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
    }

    public function testIdsMatch()
    {
        $this->service->method("contains")
            ->with("/foo/4ee8e29d45851")
            ->willReturn(true);

        $this->app->error(function (\Exception $e, $code) {
            $errorMessage = "The `id` in the body must match the `id` in the URI";
            $this->assertEquals($errorMessage, $e->getMessage());
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("PUT", "/foo/4ee8e29d45851", array(), array(), $headers, '{"id":"bar"}');
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}
