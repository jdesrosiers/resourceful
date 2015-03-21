<?php

namespace JDesrosiers\Silex\Controller\Test;

use JDesrosiers\Silex\Controller\GetResourceController;
use JDesrosiers\Silex\Resourceful;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class GetResourceControllerTest extends PHPUnit_Framework_TestCase
{
    private $app;
    private $service;
    private $client;

    public function setUp()
    {
        $this->app = new Resourceful();
        $this->app["debug"] = true;

        $this->app->get("/schema/{type}", function () {
            // No Op
        })->bind("schema");

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

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/error\"", $response->headers->get("Content-Type"));
        $this->assertEquals(0, $content->code);
        $this->assertEquals("Not Found", $content->message);
    }

    public function testGetError()
    {
        $this->service->method("contains")
            ->with("/foo/4ee8e29d45851")
            ->willReturn(true);

        $this->service->method("fetch")
            ->with("/foo/4ee8e29d45851")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/error\"", $response->headers->get("Content-Type"));
        $this->assertEquals(0, $content->code);
        $this->assertEquals("Failed to retrieve resource", $content->message);
    }
}
