<?php

namespace JDesrosiers\Silex\Controller\Test;

use JDesrosiers\Silex\Controller\DeleteResourceController;
use JDesrosiers\Silex\Resourceful;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class DeleteResourceControllerTest extends PHPUnit_Framework_TestCase
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
        $this->app->delete("/foo/{id}", new DeleteResourceController($this->service));

        $this->client = new Client($this->app);
    }

    public function testDelete()
    {
        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("DELETE", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEquals("", $response->getContent());
    }

    public function testDeleteError()
    {
        $this->service->method("delete")
            ->willReturn(false);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("DELETE", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/error\"", $response->headers->get("Content-Type"));
        $this->assertEquals(0, $content->code);
        $this->assertEquals("Failed to delete resource", $content->message);
    }
}
