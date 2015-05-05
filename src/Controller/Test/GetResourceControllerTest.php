<?php

namespace JDesrosiers\Resourceful\Controller\Test;

use JDesrosiers\Resourceful\Controller\GetResourceController;
use PHPUnit_Framework_TestCase;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class GetResourceControllerTest extends PHPUnit_Framework_TestCase
{
    private $app;
    private $service;
    private $client;

    public function setUp()
    {
        $this->app = new Application();
        $this->app["debug"] = true;

        $this->service = $this->getMock("Doctrine\Common\Cache\Cache");
        $this->app->get("/foo/{id}", new GetResourceController($this->service));

        $this->client = new Client($this->app);
    }

    public function testGet()
    {
        $foo = new \stdClass();
        $foo->id = "4ee8e29d45851";

        $this->service->method("contains")
            ->with("/foo/4ee8e29d45851")
            ->willReturn(true);

        $this->service->method("fetch")
            ->with("/foo/4ee8e29d45851")
            ->willReturn($foo);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString('{"id":"4ee8e29d45851"}', $response->getContent());
    }

    public function testGetNotFound()
    {
        $this->service->method("contains")
            ->with("/foo/4ee8e29d45851")
            ->willReturn(false);

        $this->app->error(function (\Exception $e, $code) {
            $this->assertEquals("Not Found", $e->getMessage());
        });

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
            ->with("/foo/4ee8e29d45851")
            ->willReturn(true);

        $this->service->method("fetch")
            ->with("/foo/4ee8e29d45851")
            ->willReturn(false);

        $this->app->error(function (\Exception $e, $code) {
            $this->assertEquals("Failed to retrieve resource", $e->getMessage());
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
    }
}
