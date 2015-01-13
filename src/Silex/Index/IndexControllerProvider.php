<?php

namespace JDesrosiers\Silex\Index;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;

class IndexControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app["controllers_factory"];

        $controller->get("/", array($this, "get"));

        $app["schema-store"]->add("/schema/framework-index", json_decode(file_get_contents(__DIR__ . "/index.json")));
        $app["schema-store"]->add("/schema/index", json_decode(file_get_contents("{$app["schemaPath"]}/index.json")));

        return $controller;
    }

    public function get(Application $app)
    {
        $index = array(
            "title" => $app["index.title"],
            "description" => $app["index.description"],
        );

        return $app->json(
            $index,
            Response::HTTP_OK,
            array("Content-Type" => "application/json; profile=/schema/index")
        );
    }
}
