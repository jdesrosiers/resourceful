<?php

namespace JDesrosiers\Resourceful\SchemaControllerProvider;

use JDesrosiers\Resourceful\Controller\GetResourceController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SchemaControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $resource = $app["controllers_factory"];

        $resource->get("/{type}", new GetResourceController($app["resourceful.schemas"], "application/schema+json"))
            ->assert("type", ".+")
            ->bind("schema");

        $resource->after(function (Request $request, Response $response, Application $app) {
            if ($response->isSuccessful()) {
                $schema = json_decode($response->getContent(), true);
                $app["json-schema.describedBy"] = array_key_exists('$schema', $schema)
                    ? $schema['$schema']
                    : $app["resourceful.defaultSchemaVersion"];
            }
        });

        return $resource;
    }
}
