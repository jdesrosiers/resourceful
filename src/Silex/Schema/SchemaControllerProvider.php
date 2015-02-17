<?php

namespace JDesrosiers\Silex\Schema;

use JDesrosiers\Silex\Generic\GetResourceController;
use Silex\Application;
use Silex\ControllerProviderInterface;

class SchemaControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app["controllers_factory"];

        $schema = "http://json-schema.org/hyper-schema";
        $contentType = "application/schema+json";
        $controller->get("/{path}", new GetResourceController($app["schemaService"], $schema, $contentType))
            ->assert("path", ".+")
            ->bind("schema");

        return $controller;
    }
}
