<?php

namespace JDesrosiers\Silex\Generic;

use Doctrine\Common\Cache\Cache;
use Silex\Application;
use Silex\ControllerProviderInterface;

class GenericControllerProvider implements ControllerProviderInterface
{
    private $type;
    private $service;

    public function __construct($type, Cache $service)
    {
        $this->type = strtolower($type);
        $this->service = $service;
    }

    public function connect(Application $app)
    {
        list($type, $controller) = $app["typeFactory"]($this->type);
        $controller->get("/{id}", new GetResourceController($type))->bind($type->schema);
        $controller->put("/{id}", new PutResourceController($type));
        $controller->delete("/{id}", new DeleteResourceController($type));
        $controller->post("/", new CreateResourceController($type));

        return $controller;
    }
}
