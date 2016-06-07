<?php

namespace JDesrosiers\Resourceful\IndexControllerProvider\Test;

use JDesrosiers\Resourceful\IndexControllerProvider\IndexControllerProvider;
use JDesrosiers\Resourceful\Resourceful;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\ResourcefulServiceProvider;
use JDesrosiers\Resourceful\SchemaControllerProvider\SchemaControllerProvider;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class IndexControllerProviderTest extends PHPUnit_Framework_TestCase
{
    private $app;
    private $client;

    public function setUp()
    {
        $this->app = new Resourceful();
        $this->app["debug"] = true;

        $this->app->register(new ResourcefulServiceProvider(), array(
            "resourceful.schemaStore" => $this->getMock("Doctrine\Common\Cache\Cache"),
        ));

        $this->app->mount("/schema", new SchemaControllerProvider());
        $this->app->flush();

        $this->service = $this->getMock("Doctrine\Common\Cache\Cache");
        $this->app->mount("/", new IndexControllerProvider($this->service));

        $this->client = new Client($this->app);
    }

    public function testGet()
    {
        $index = '{"title":"My API", "description":"This is my fantastic API"}';

        $this->service->method("contains")
            ->with("/")
            ->willReturn(true);

        $this->service->method("fetch")
            ->with("/")
            ->willReturn(json_decode($index));

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/index\"", $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString($index, $response->getContent());
    }
}
