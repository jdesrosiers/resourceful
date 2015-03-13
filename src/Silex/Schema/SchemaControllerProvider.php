<?php

namespace JDesrosiers\Silex\Schema;

use JDesrosiers\Silex\Generic\GetResourceController;
use JDesrosiers\Silex\Schema\DescribedBy;
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
        $controller->after(new DescribedBy("http://json-schema.org/hyper-schema"));

        $controller->get("/{type}", new GetResourceController($this->service, "application/schema+json"))
            ->assert("type", ".+")
            ->bind("schema");

        return $controller;
    }
}
