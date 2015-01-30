<?php

namespace JDesrosiers\Silex\Index\Test;

use JDesrosiers\Silex\Index\IndexControllerProvider;
use JDesrosiers\Silex\Schema\JsonSchemaServiceProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class IndexControllerProviderTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $client;

    public function setUp()
    {
        $this->app = new Application();
        $this->app["debug"] = true;

        $this->app["index.title"] = "My API";
        $this->app["index.description"] = "This is my fantastic API";

        $this->app["schemaService"] = $this->getMock("Doctrine\Common\Cache\Cache");
        $this->app["schemaService"]->method("contains")
            ->with("index")
            ->willReturn(true);

        $this->app->register(new JsonSchemaServiceProvider());
        $this->app->register(new TwigServiceProvider());

        $this->app->mount("/", new IndexControllerProvider());

        $this->client = new Client($this->app);
    }

    public function testGet()
    {
        $index = new \stdClass();
        $index->title = $this->app["index.title"];
        $index->description = $this->app["index.description"];

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/index\"", $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString(json_encode($index), $response->getContent());
    }
}
