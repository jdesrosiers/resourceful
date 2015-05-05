<?php

namespace JDesrosiers\Resourceful\Controller\Test;

use JDesrosiers\Resourceful\Controller\DeleteResourceController;
use PHPUnit_Framework_TestCase;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class DeleteResourceControllerTest extends PHPUnit_Framework_TestCase
{
    private $app;
    private $service;
    private $client;

    public function setUp()
    {
        $this->app = new Application();
        $this->app["debug"] = true;

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

        $this->app->error(function (\Exception $e, $code) {
            $this->assertEquals("Failed to delete resource", $e->getMessage());
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("DELETE", "/foo/4ee8e29d45851", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
    }
}
