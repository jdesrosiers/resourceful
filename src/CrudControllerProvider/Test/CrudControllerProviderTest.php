<?php

namespace JDesrosiers\Resourceful\CrudControllerProvider;

use JDesrosiers\Resourceful\CrudControllerProvider\CrudControllerProvider;
use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider;
use JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class CrudControllerProviderTest extends PHPUnit_Framework_TestCase
{
    private $app;
    private $service;
    private $client;

    public function setUp()
    {
        $this->app = new Resourceful();
        $this->app["debug"] = true;

        $this->app->register(new ResourcefulServiceProvider(), [
            "resourceful.schemas" => $this->getMockBuilder("Doctrine\Common\Cache\Cache")->getMock(),
        ]);

        $this->app->mount("/schema", new SchemaControllerProvider());
        $this->app->flush();

        $this->service = $this->getMockBuilder("Doctrine\Common\Cache\Cache")->getMock();
        $this->app->mount("/foo", new CrudControllerProvider("foo", $this->service));

        $this->client = new Client($this->app);
    }

    public function testRetreive()
    {
        $this->service->method("contains")
            ->with("/foo/4ee8e29d45851")
            ->willReturn(true);

        $headers = [
            "HTTP_ACCEPT" => "application/json",
        ];
        $this->client->request("GET", "/foo/4ee8e29d45851", [], [], $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/foo\"", $response->headers->get("Content-Type"));
    }

    public function testErrorHandling()
    {
        $headers = [
            "HTTP_ACCEPT" => "application/json",
        ];
        $this->client->request("GET", "/foo/4ee8e29d45851", [], [], $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/error\"", $response->headers->get("Content-Type"));
    }

    public function testUpdate()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->service->method("contains")
            ->with("/foo/$foo->id")
            ->willReturn(true);

        $headers = [
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        ];
        $this->client->request("PUT", "/foo/$foo->id", [], [], $headers, "{\"id\":\"$foo->id\"}");
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/foo\"", $response->headers->get("Content-Type"));
        $this->assertFalse($response->headers->has("Location"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }

    public function testDelete()
    {
        $headers = [
            "HTTP_ACCEPT" => "application/json",
        ];
        $this->client->request("DELETE", "/foo/4ee8e29d45851", [], [], $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertFalse($response->headers->has("Content-Type"));
        $this->assertEquals("", $response->getContent());
    }

    public function testCreate()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->app["uniqid"] = $foo->id;

        $headers = [
            "HTTP_ACCEPT" => "application/json",
            "CONTENT_TYPE" => "application/json"
        ];
        $this->client->request("POST", "/foo/", [], [], $headers, "{}");
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/foo\"", $response->headers->get("Content-Type"));
        $this->assertEquals("/foo/$foo->id", $response->headers->get("Location"));
        $this->assertJsonStringEqualsJsonString("{\"id\":\"$foo->id\"}", $response->getContent());
    }
}
