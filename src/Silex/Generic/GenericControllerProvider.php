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
        $controller->post("/", array($this, "create"));
        $controller->put("/{id}", array($this, "put"));
        $controller->delete("/{id}", array($this, "delete"));

        $app->before(function (Request $request, Application $app) {
            if (!$app["schemaService"]->contains($this->type)) {
                $replacements = array(
                    "type" => $this->type,
                    "title" => ucfirst($this->type),
                );

                $app["schemaService"]->save(
                    $this->type,
                    json_decode($app["twig"]->render("generic.json.twig", $replacements))
                );
            }

            $app["schema-store"]->add("/schema/$this->type", $app["schemaService"]->fetch($this->type));
        });

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

        return $app->json(
            $resource,
            Response::HTTP_OK,
            array("Content-Type" => "application/json; profile=/schema/$this->type")
        );
    }

    public function create(Application $app, Request $request)
    {
        $data = json_decode($request->getContent());
        $data->id = $app["uniqid"];

        return $this->write($app, $data->id, $data);
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

        $isCreated = !$this->service->contains($id);

        $success = $this->service->save($id, $data);
        if ($success === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to save resource");
        }

        $response = $app->json(
            $data,
            Response::HTTP_OK,
            array("Content-Type" => "application/json; profile=/schema/$this->type")
        );

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
