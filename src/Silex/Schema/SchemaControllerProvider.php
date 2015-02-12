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
        if (!$app["schemaService"]->contains($path)) {
            throw new NotFoundHttpException();
        }

        $app["json-schema.describedBy"] = "http://json-schema.org/hyper-schema";
        return $app->json(
            $app["schemaService"]->fetch($path),
            Response::HTTP_OK,
            array("Content-Type" => "application/schema+json")
        );
    }
}
