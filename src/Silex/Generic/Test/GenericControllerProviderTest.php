<?php

namespace JDesrosiers\Silex\Generic\Test;

use JDesrosiers\Silex\Generic\GenericControllerProvider;
use JDesrosiers\Silex\Schema\JsonSchemaServiceProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class GenericControllerProviderTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $service;
    private $client;

    public function setUp()
    {
        $this->app = new Application();
        $this->app["debug"] = true;

        $this->app["schemaService"] = $this->getMock("Doctrine\Common\Cache\Cache");
        $this->app["schemaService"]->method("contains")
            ->with("foo")
            ->willReturn(true);

        $this->app->register(new UrlGeneratorServiceProvider());
        $this->app->register(new JsonSchemaServiceProvider());
        $this->app->register(new TwigServiceProvider());

        $this->service = $this->getMock("Doctrine\Common\Cache\Cache");

        $this->app->mount("/foo", new GenericControllerProvider("foo", $this->service));

        $this->client = new Client($this->app);
    }

    public function testGet()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->service->method("contains")
            ->with("4ee8e29d45851")
            ->willReturn(true);

        $this->service->method("fetch")
            ->with("4ee8e29d45851")
            ->willReturn($foo);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/foo\"", $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString('{"id":"4ee8e29d45851"}', $response->getContent());
    }

    public function testGetNotFound()
    {
        $this->service->method("contains")
            ->with("4ee8e29d45851")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetError()
    {
        $this->service->method("contains")
            ->with("4ee8e29d45851")
            ->willReturn(true);

        $this->service->method("fetch")
            ->with("4ee8e29d45851")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
    }

    public function testCreate()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->app["uniqid"] = $foo->id;

        $this->service->method("contains")
            ->with($foo->id)
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("POST", "/foo/", array(), array(), $headers, '{}');
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/foo\"", $response->headers->get("Content-Type"));
        $this->assertEquals("/foo/$foo->id", $response->headers->get("Location"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }

    public function testBadCreateRequest()
    {
        $this->app["uniqid"] = uniqid();

        $schema = file_get_contents(__DIR__ . "/foo.json");
        $this->app["schemaService"]->method("fetch")
            ->with("foo")
            ->willReturn(json_decode($schema));

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

        $this->service->method("contains")
            ->with($foo->id)
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("PUT", "/foo/$foo->id", array(), array(), $headers, "{\"id\":\"$foo->id\"}");
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/foo\"", $response->headers->get("Content-Type"));
        $this->assertEquals("/foo/$foo->id", $response->headers->get("Location"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }

    public function testSaveError()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->service->method("contains")
            ->with($foo->id)
            ->willReturn(false);

        $this->service->method("save")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        );
        $this->client->request("PUT", "/foo/$foo->id", array(), array(), $headers, "{\"id\":\"$foo->id\"}");
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
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

    public function testDeleteError()
    {
        $this->service->method("delete")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("DELETE", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
    }
}
