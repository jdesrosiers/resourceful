<?php

namespace JDesrosiers\Resourceful\Schema\Test;

use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\Schema\SchemaControllerProvider;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class SchemaControllerProviderTest extends PHPUnit_Framework_TestCase
{
    private $client;

    public function setUp()
    {
        $this->app = new Resourceful();
        $this->app["debug"] = true;

        $this->app["schemaService"] = $this->getMock("Doctrine\Common\Cache\Cache");
        $this->app->mount("/schema", new SchemaControllerProvider($this->app["schemaService"]));

        $this->client = new Client($this->app);
    }

    public function testGet()
    {
        $this->app["schemaService"]->method("contains")
            ->with("/schema/foo")
            ->willReturn(true);

        $this->app["schemaService"]->method("fetch")
            ->with("/schema/foo")
            ->willReturn(new \stdClass());

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/schema/foo", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $expectedContentType = "application/schema+json; profile=\"http://json-schema.org/hyper-schema\"";
        $this->assertEquals($expectedContentType, $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString('{}', $response->getContent());
    }

    public function testGetNotFound()
    {
        $this->app["schemaService"]->method("fetch")
            ->with("/schema/bar")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/schema/bar", array(), array(), $headers);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/error\"", $response->headers->get("Content-Type"));
        $this->assertEquals('Not Found', $content->message);
    }
}
