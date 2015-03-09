<?php

namespace JDesrosiers\Silex\Schema\Test;

use JDesrosiers\Silex\Schema\SchemaControllerProvider;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class SchemaControllerProviderTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $schemaService;

    public function setUp()
    {
        $this->app = new Application();
        $this->app["debug"] = true;
        $this->app["rootPath"] = __DIR__;

        $this->schemaService = $this->getMock("Doctrine\Common\Cache\Cache");
        $this->app->mount("/schema", new SchemaControllerProvider($this->schemaService));

        $this->client = new Client($this->app);
    }

    public function testGet()
    {
        $this->schemaService->method("contains")
            ->with("/schema/foo")
            ->willReturn(true);

        $this->schemaService->method("fetch")
            ->with("/schema/foo")
            ->willReturn(new \stdClass());

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/schema/foo", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/schema+json", $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString('{}', $response->getContent());
    }

    public function testGetNotFound()
    {
        $this->schemaService->method("fetch")
            ->with("/schema/bar")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/schema/bar", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
