<?php

namespace JDesrosiers\Silex\Generic;

use JDesrosiers\App\Service\GenericService;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GenericControllerProvider implements ControllerProviderInterface
{
    private $app;
    private $type;
    private $service;

    public function __construct($type, GenericService $service)
    {
        $this->type = $type;
        $this->service = $service;
    }

    public function connect(Application $app)
    {
        $this->app = $app;

        $controller = $this->app["controllers_factory"];

        $controller->get("/", array($this, "query"));
        $controller->get("/{id}", array($this, "get"))->bind($this->type);
        $controller->post("/", array($this, "create"));
        $controller->put("/{id}", array($this, "put"));
        $controller->delete("/{id}", array($this, "delete"));

        return $controller;
    }

    public function query()
    {
        $collection = array(
            "collection" => array_values($this->service->query()),
        );

        return $this->app->json(
            $collection,
            Response::HTTP_OK,
            array("Content-Type" => "application/json; profile=/schema/{$this->type}Collection")
      );
    }

    public function get($id)
    {
        $resource = $this->service->get($id);

        if ($resource === null) {
            throw new NotFoundHttpException();
        }

        return $this->app->json(
            $resource,
            Response::HTTP_OK,
            array("Content-Type" => "application/json; profile=/schema/$this->type")
        );
    }

    public function create(Request $request)
    {
        $object = json_decode($request->getContent());
        $id = $this->app["genericService.uniqid"];
        $object->{$this->type . "Id"} = $id;

        return $this->write($id, $object);
    }

    public function put(Request $request, $id)
    {
        return $this->write($id, json_decode($request->getContent()));
    }

    private function write($id, $object)
    {
        $result = $this->service->put($id, $object);

        $response = $this->app->json(
            $object,
            Response::HTTP_OK,
            array("Content-Type" => "application/json; profile=/schema/$this->type")
        );

        if ($result === GenericService::CREATED) {
            $response->setStatusCode(Response::HTTP_CREATED);
            $response->headers->set("Location", $this->app["url_generator"]->generate($this->type, array("id" => $id)));
        }

        return $response;
    }

    public function delete($id)
    {
        $this->service->delete($id);

        return Response::create("", Response::HTTP_NO_CONTENT);
    }
}
