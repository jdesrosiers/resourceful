<?php

namespace JDesrosiers\Silex\Generic;

use Doctrine\Common\Cache\Cache;
use JDesrosiers\Silex\Schema\AddSchema;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Twig_Loader_Filesystem;

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
        $controller = $app["controllers_factory"];

        $controller->get("/{id}", array($this, "get"))->bind($this->type);
        $controller->put("/{id}", array($this, "put"));
        $controller->delete("/{id}", array($this, "delete"));

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $replacements = array("type" => $this->type, "title" => ucfirst($this->type));
        $controller->before(new AddSchema($this->type, $replacements));

        return $controller;
    }

    public function get(Application $app, $id)
    {
        if (!$this->service->contains($id)) {
            throw new NotFoundHttpException("Not Found");
        }

        $resource = $this->service->fetch($id);
        if ($resource === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to retrieve resource");
        }

        $app["json-schema.describedBy"] = "/schema/$this->type";
        return $app->json($resource);
    }

    public function put(Application $app, Request $request, $id)
    {
        $requestJson = $request->getContent() ?: "{}";
        $data = json_decode($requestJson);

        if ($id !== $data->id) {
            throw new BadRequestHttpException("The `id` in the body must match the `id` in the URI");
        }
        $schema = $app["json-schema.schema-store"]->get("/schema/$this->type");
        $validation = $app["json-schema.validator"]->validate($data, $schema);
        if (!$validation->valid) {
            throw new BadRequestHttpException(json_encode($validation->errors));
        }

        $isCreated = !$this->service->contains($id);

        $success = $this->service->save($id, $data);
        if ($success === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to save resource");
        }

        $app["json-schema.describedBy"] = "/schema/$this->type";
        return $app->json($data, $isCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    public function delete($id)
    {
        $success = $this->service->delete($id);
        if ($success === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to delete resource");
        }

        return Response::create("", Response::HTTP_NO_CONTENT);
    }
}
