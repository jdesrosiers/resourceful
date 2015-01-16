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

        if (!$app["schemaService"]->has("index")) {
            $app["generateSchema"]("index", __DIR__ . "/index.json");
        }
        $app["schema-store"]->add("/schema/index", $app["schemaService"]->get("index"));

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
