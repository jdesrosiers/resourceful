<?php

namespace JDesrosiers\Resourceful\Schema;

use JDesrosiers\Resourceful\Controller\GetResourceController;
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
        $resource = $app["resources_factory"]("http://json-schema.org/hyper-schema");

        $resource->get("/{type}", new GetResourceController($this->service, "application/schema+json"))
            ->assert("type", ".+")
            ->bind("schema");

        return $resource;
    }
}
