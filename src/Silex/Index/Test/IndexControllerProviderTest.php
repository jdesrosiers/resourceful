<?php

namespace JDesrosiers\Silex\Index\Test;

use JDesrosiers\Doctrine\Cache\FileCache;
use JDesrosiers\Silex\Index\IndexControllerProvider;
use JDesrosiers\Silex\Resourceful;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class IndexControllerProviderTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $client;

    public function setUp()
    {
        $this->app = new Resourceful();
        $this->app["debug"] = true;

        $this->app["schemaService"] = $this->getMock("Doctrine\Common\Cache\Cache");
        $this->app->get("/schema/{type}", function () {
            // No Op
        })->bind("schema");

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
