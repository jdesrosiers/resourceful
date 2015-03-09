<?php

namespace JDesrosiers\Silex\Schema;

use JDesrosiers\Silex\Generic\GetResourceController;
use JDesrosiers\Silex\Generic\TypeContext;
use Silex\Application;
use Silex\ControllerProviderInterface;

class SchemaControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app["controllers_factory"];

        $type = new TypeContext($app["schemaService"], "http://json-schema.org/hyper-schema");
        $controller->get("/{type}", new GetResourceController($type, "application/schema+json"))
            ->assert("type", ".+")
            ->bind("schema");

        return $controller;
    }
}
