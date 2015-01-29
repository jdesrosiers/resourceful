<?php

namespace JDesrosiers\Silex\Index;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Loader_Filesystem;

class IndexControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));

        $controller = $app["controllers_factory"];

        $controller->get("/", array($this, "get"));

        $app->before(function (Request $request, Application $app) {
            if (!$app["schemaService"]->contains("index")) {
                $app["schemaService"]->save("index", json_decode($app["twig"]->render("index.json.twig")));
            }

            $app["schema-store"]->add("/schema/index", $app["schemaService"]->fetch("index"));
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
            array("Content-Type" => "application/json; profile=\"/schema/index\"")
        );
    }
}
