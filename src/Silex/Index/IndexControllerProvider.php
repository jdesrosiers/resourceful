<?php

namespace JDesrosiers\Silex\Index;

use JDesrosiers\Silex\Schema\AddSchema;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Twig_Loader_Filesystem;

class IndexControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app["controllers_factory"];

        $controller->get("/", array($this, "get"));

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $controller->before(new AddSchema("index", "index"));

        return $controller;
    }

    public function get(Application $app)
    {
        $index = array(
            "title" => $app["index.title"],
            "description" => $app["index.description"],
        );

        $app["json-schema.describedBy"] = "/schema/index";
        return $app->json($index);
    }
}
