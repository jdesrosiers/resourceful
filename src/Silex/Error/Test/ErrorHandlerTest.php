<?php

namespace JDesrosiers\Silex\Error\Test;

use JDesrosiers\Doctrine\Cache\FileCache;
use JDesrosiers\Silex\Error\ErrorHandlerServiceProvider;
use JDesrosiers\Silex\Resourceful;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $app;
    protected $client;

    public function setUp()
    {
        $this->app = new Resourceful();
        $this->app["debug"] = true;

        $this->app["schemaService"] = new FileCache(__DIR__);
        $this->app->get("/schema/{type}", function () {
            // No Op
        })->bind("schema");

        $this->app->register(new ErrorHandlerServiceProvider());

        $this->client = new Client($this->app);
    }

    public function testHandleError()
    {
        $this->app->get("/foo", function () {
            throw new NotFoundHttpException("Not Found", null, 4);
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo", array(), array(), $headers);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/error\"", $response->headers->get("Content-Type"));
        $this->assertEquals(4, $content->code);
        $this->assertEquals("Not Found", $content->message);
        $this->assertInternalType("string", $content->trace);
    }
}
