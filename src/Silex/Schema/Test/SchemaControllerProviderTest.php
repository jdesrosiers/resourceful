<?php

namespace JDesrosiers\Silex\Schema\Test;

use JDesrosiers\Silex\Schema\SchemaControllerProvider;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class SchemaControllerProviderTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    public function setUp()
    {
        $this->app = new Application();
        $this->app["debug"] = true;
        $this->app["rootPath"] = __DIR__;

        $this->app["schemaService"] = $this->getMock("JDesrosiers\App\Service\GenericService");
        $this->app->mount("/schema", new SchemaControllerProvider());

        $this->client = new Client($this->app);
    }

    public function testGet()
    {
        $this->app["schemaService"]->method("contains")
            ->with("foo")
            ->willReturn(true);

        $this->app["schemaService"]->method("fetch")
            ->with("foo")
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
        $this->app["schemaService"]->method("fetch")
            ->with("bar")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/schema/bar", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
