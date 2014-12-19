<?php

use JDesrosiers\App\Foo\Foo;
use JDesrosiers\Silex\Foo\FooControllerProvider;
use JDesrosiers\Silex\MyApplication;
use Symfony\Component\HttpKernel\Client;

class FooControllerTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        $this->app = new MyApplication();
        $this->app["debug"] = true;
        $this->app->mount("/foo", new FooControllerProvider());

        $this->app["foo"] = $this->getMock("JDesrosiers\App\Foo\FooServiceInterface");
    }

    public function testGetFoo()
    {
        $foo = new Foo();
        $foo->fooBar = 3;

        $this->app["foo"]
            ->method("get")
            ->with(1)
            ->willReturn($foo);

        $headers = array(
            "HTTP_ACCEPT" => "application/json",
        );

        $client = new Client($this->app);
        $client->request("GET", "/foo/1");

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("application/json; profile=/schema/foo", $response->headers->get("Content-Type"));
        $this->assertJsonStringEqualsJsonString('{"bar":3}', $response->getContent());
    }
}
