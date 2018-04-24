<?php

namespace JDesrosiers\Resourceful\SchemaControllerProvider\Test;

use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider;
use JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class SchemaControllerProviderTest extends PHPUnit_Framework_TestCase
{
    private $app;
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

        $this->client = new Client($this->app);
    }

    public function testGet()
    {
        $this->app["resourceful.schemas"]->method("contains")
            ->willReturn(true);

        $this->app["resourceful.schemas"]->method("fetch")
            ->willReturn(new \stdClass());

        $headers = [
            "HTTP_ACCEPT" => "application/json",
        ];
        $this->client->request("GET", "/schema/foo", [], [], $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $expectedContentType = "application/schema+json; profile=\"http://json-schema.org/hyper-schema\"";
        $this->assertEquals($expectedContentType, $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString('{}', $response->getContent());
    }

    public function testGetNotFound()
    {
        $this->app["resourceful.schemas"]->method("fetch")
            ->will($this->returnValueMap(["/schema/error" => true]));

        $headers = [
            "HTTP_ACCEPT" => "application/json",
        ];
        $this->client->request("GET", "/schema/bar", [], [], $headers);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/error\"", $response->headers->get("Content-Type"));
        $this->assertEquals('Not Found', $content->message);
    }
}
