<?php

namespace JDesrosiers\Silex\Generic;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResourcesFactory
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function __invoke($schema)
    {
        $resource = $this->app["controllers_factory"];

        $resource->after(function (Request $request, Response $response, Application $app) use ($schema) {
            if ($response->isSuccessful()) {
                $app["json-schema.describedBy"] = $schema;
            }
        });

        return $resource;
    }
}
