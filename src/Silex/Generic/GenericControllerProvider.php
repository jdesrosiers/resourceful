<?php

namespace JDesrosiers\Silex\Generic;

use JDesrosiers\App\Service\GenericService;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GenericControllerProvider implements ControllerProviderInterface
{
    private $type;
    private $service;

    public function __construct($type, GenericService $service)
    {
        $this->type = $type;
        $this->service = $service;
    }

    public function connect(Application $app)
    {
        $controller = $app["controllers_factory"];

        $controller->get("/", array($this, "query"));
        $controller->get("/{id}", array($this, "get"))->bind($this->type);
        $controller->post("/", array($this, "create"));
        $controller->put("/{id}", array($this, "put"));
        $controller->delete("/{id}", array($this, "delete"));

        $app["schema-store"]->add("/schema/$this->type", $app["schemaService"]->get($this->type));
        $app["schema-store"]->add(
            "/schema/{$this->type}Collection",
            $app["schemaService"]->get("{$this->type}Collection")
        );

        return $controller;
    }

    public function query(Application $app)
    {
        $collection = array(
            "collection" => array_values($this->service->query()),
        );

        return $app->json(
            $collection,
            Response::HTTP_OK,
            array("Content-Type" => "application/json; profile=/schema/{$this->type}Collection")
      );
    }

    public function get(Application $app, $id)
    {
        $resource = $this->service->get($id);

        if ($resource === null) {
            throw new NotFoundHttpException();
        }

        return $app->json(
            $resource,
            Response::HTTP_OK,
            array("Content-Type" => "application/json; profile=/schema/$this->type")
        );
    }

    public function create(Application $app, Request $request)
    {
        $data = json_decode($request->getContent());
        $id = $app["genericService.uniqid"];
        $data->id = $id;

        return $this->write($app, $id, $data);
    }

    public function put(Application $app, Request $request, $id)
    {
        return $this->write($app, $id, json_decode($request->getContent()));
    }

    private function write(Application $app, $id, $data)
    {
        $validation = $app["validator"]->validate($data, $app["schema-store"]->get("/schema/$this->type"));
        if (!$validation->valid) {
            throw new BadRequestHttpException(json_encode($validation->errors));
        }

        $result = $this->service->put($id, $data);

        $response = $app->json(
            $data,
            Response::HTTP_OK,
            array("Content-Type" => "application/json; profile=/schema/$this->type")
        );

        if ($result === GenericService::CREATED) {
            $response->setStatusCode(Response::HTTP_CREATED);
            $response->headers->set("Location", $app["url_generator"]->generate($this->type, array("id" => $id)));
        }

        return $response;
    }

    public function delete($id)
    {
        $this->service->delete($id);

        return Response::create("", Response::HTTP_NO_CONTENT);
    }
}
