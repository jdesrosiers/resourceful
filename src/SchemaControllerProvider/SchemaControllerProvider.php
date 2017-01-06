<?php

namespace JDesrosiers\Resourceful\SchemaControllerProvider;

use JDesrosiers\Resourceful\Controller\GetResourceController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class SchemaControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $resource = $app["resources_factory"]("http://json-schema.org/hyper-schema");

        $resource->get("/{type}", new GetResourceController($app["resourceful.schemas"], "application/schema+json"))
            ->assert("type", ".+")
            ->bind("schema");

        return $resource;
    }
}
