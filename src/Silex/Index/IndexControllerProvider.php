<?php

namespace JDesrosiers\Silex\Index;

use JDesrosiers\App\Service\GenericService;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app["controllers_factory"];

        $controller->get("/", array($this, "get"));

        $app->before(function (Request $request, Application $app) {
            list($status, $schema) = $app["schemaService"]->get("index");

            if ($status === GenericService::NOT_FOUND) {
                $schema = $app["generateSchema"](__DIR__ . "/index.json");
                $app["schemaService"]->put("index", $schema);
            }

            $app["schema-store"]->add("/schema/index", $schema);
        });

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
