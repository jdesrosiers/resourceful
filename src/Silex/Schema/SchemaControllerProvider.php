<?php

namespace JDesrosiers\Silex\Schema;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SchemaControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller = $app["controllers_factory"];

        $controller->get("/{path}", array($this, "get"))
            ->assert("path", ".+")
            ->bind("schema");

        return $controller;
    }

    public function get(Application $app, $path)
    {
        $schema = $app["schemaService"]->get($path);
        if ($schema === null) {
            throw new NotFoundHttpException();
        }

        return $app->json($schema, Response::HTTP_OK, array("Content-Type" => "application/schema+json"));
    }
}
