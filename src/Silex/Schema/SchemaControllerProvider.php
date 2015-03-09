<?php

namespace JDesrosiers\Silex\Schema;

use JDesrosiers\Silex\Generic\GetResourceController;
use JDesrosiers\Silex\Generic\TypeContext;
use Silex\Application;
use Silex\ControllerProviderInterface;

class SchemaControllerProvider implements ControllerProviderInterface
{
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function connect(Application $app)
    {
        $controller = $app["controllers_factory"];

        $type = new TypeContext($this->service, "http://json-schema.org/hyper-schema");
        $controller->get("/{type}", new GetResourceController($type, "application/schema+json"))
            ->assert("type", ".+")
            ->bind("schema");

        return $controller;
    }
}
