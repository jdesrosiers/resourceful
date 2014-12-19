<?php

namespace JDesrosiers\Silex\Foo;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;

class FooControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $fooController = $app["controllers_factory"];

        $fooController->get("/{id}", array($this, "get"));

        $app["serializer.builder"]->addMetadataDir(__DIR__, "JDesrosiers\App\Foo");

        return $fooController;
    }

    public function get(Application $app, $id)
    {
        return $app["conneg"]->createResponse(
            $app["foo"]->get($id),
            Response::HTTP_OK,
            array("Content-Type" => "application/json; profile=/schema/foo")
        );
    }
}
