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

        $schema = new \stdClass();
        $schema->{'$schema'} = "http://json-schema.org/draft-04/hyper-schema";
        $this->app["resourceful.schemas"]->method("fetch")
            ->willReturn($schema);

        $headers = [
            "HTTP_ACCEPT" => "application/json",
        ];
        $this->client->request("GET", "/schema/foo", [], [], $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/schema+json", $response->headers->get("Content-Type"));
        $this->assertEquals("<http://json-schema.org/draft-04/hyper-schema>; rel=\"describedby\"", $response->headers->get("Link"));
        $this->assertJsonStringEqualsJsonString(json_encode($schema), $response->getContent());
    }

    public function testGetWithDefaultSchema()
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
        $this->assertEquals("application/schema+json", $response->headers->get("Content-Type"));
        $this->assertEquals("<http://json-schema.org/hyper-schema>; rel=\"describedby\"", $response->headers->get("Link"));
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
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));
        $this->assertEquals("</schema/error>; rel=\"describedby\"", $response->headers->get("Link"));
        $this->assertEquals('Not Found', $content->message);
    }
}
