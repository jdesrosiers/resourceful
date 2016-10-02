<?php

namespace JDesrosiers\Resourceful\SchemaControllerProvider\Test;

use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider;
use JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider;
use PHPUnit_Framework_TestCase;
use Silex\Provider\TwigServiceProvider;
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

        $this->app->register(new TwigServiceProvider());
        $this->app->register(new ResourcefulServiceProvider(), array(
            "resourceful.schemaStore" => $this->getMockBuilder("Doctrine\Common\Cache\Cache")->getMock(),
        ));

        $this->app->mount("/schema", new SchemaControllerProvider());

        $this->client = new Client($this->app);
    }

    public function testGet()
    {
        $this->app["resourceful.schemaStore"]->method("contains")
            ->willReturn(true);

        $this->app["resourceful.schemaStore"]->method("fetch")
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
        $this->app["resourceful.schemaStore"]->method("fetch")
            ->will($this->returnValueMap(array("/schema/error" => true)));

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
