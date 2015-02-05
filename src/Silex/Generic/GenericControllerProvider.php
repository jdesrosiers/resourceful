<?php

namespace JDesrosiers\Silex\Generic;

use Doctrine\Common\Cache\Cache;
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
        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));

        $controller = $app["controllers_factory"];

        $controller->get("/{id}", array($this, "get"))->bind($this->type);
        $controller->put("/{id}", array($this, "put"));
        $controller->delete("/{id}", array($this, "delete"));

        $replacements = array("type" => $this->type, "title" => ucfirst($this->type));
        $controller->before(new AddSchema($this->type, $replacements));
        $controller->after(new SetProfile("/schema/$this->type"));

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

        return $app->json($resource);
    }

    public function put(Application $app, Request $request, $id)
    {
        $requestJson = $request->getContent() ?: "{}";
        $data = json_decode($requestJson);

        $validation = $app["validator"]->validate($data, $app["schema-store"]->get("/schema/$this->type"));
        if (!$validation->valid) {
            throw new BadRequestHttpException(json_encode($validation->errors));
        }

        $isCreated = !$this->service->contains($id);

        $success = $this->service->save($id, $data);
        if ($success === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to save resource");
        }

        $response = $app->json($data);

        if ($isCreated) {
            $response->setStatusCode(Response::HTTP_CREATED);
            $response->headers->set("Location", $app["url_generator"]->generate($this->type, array("id" => $id)));
        }

        return $response;
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
