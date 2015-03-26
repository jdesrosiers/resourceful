<?php

namespace JDesrosiers\Silex\Provider\JsonSchema\Test;

use JDesrosiers\Silex\Provider\JsonSchema\JsonSchemaServiceProvider;
use PHPUnit_Framework_TestCase;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;

class DescribedByTest extends PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = new Application();
        $this->app["debug"] = true;

        $this->app->register(new JsonSchemaServiceProvider());

        $this->client = new Client($this->app);
    }

    public function testDescribedByProfile()
    {
        $this->app->get("/foo", function (Application $app) {
            $app["json-schema.describedBy"] = "/schema/foo";

            return $app->json(new \stdClass());
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/json; profile=\"/schema/foo\"", $response->headers->get("Content-Type"));
    }

    public function testDescribedByLink()
    {
        $this->app["json-schema.correlationMechanism"] = "link";

        $this->app->get("/foo", function (Application $app) {
            $app["json-schema.describedBy"] = "/schema/foo";

            return $app->json(new \stdClass());
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("</schema/foo>; rel=\"describedBy\"", $response->headers->get("Link"));
    }

    public function testInvalidCorrelationMechanism()
    {
        $this->app["json-schema.correlationMechanism"] = "foo";

        $this->app->get("/foo", function (Application $app) {
            $app["json-schema.describedBy"] = "/schema/foo";

            return $app->json(new \stdClass());
        });

        $this->app->error(function (\Exception $e, $code) {
            $errorMessage = "json-schema.correlationMechanism must be either \"profile\" or \"link\"";
            $this->assertEquals($errorMessage, $e->getMessage());
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
    }

    public function testNoContent()
    {
        $this->app->get("/foo", function (Application $app) {
            $app["json-schema.describedBy"] = "/schema/foo";

            return Response::create("", Response::HTTP_NO_CONTENT);
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testNoDescribedBy()
    {
        $this->app->get("/foo", function (Application $app) {
            return $app->json(new \stdClass());
        });

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );
        $this->client->request("GET", "/foo", array(), array(), $headers);
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));
    }
}
