<?php

namespace JDesrosiers\Silex\Schema;

use JDesrosiers\App\Service\GenericService;
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
        list($status, $schema) = $app["schemaService"]->get($path);
        if ($status === GenericService::NOT_FOUND) {
            throw new NotFoundHttpException();
        }

        return $app->json($schema, Response::HTTP_OK, array("Content-Type" => "application/schema+json"));
    }
}
