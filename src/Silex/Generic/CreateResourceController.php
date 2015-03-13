<?php

namespace JDesrosiers\Silex\Generic;

use Doctrine\Common\Cache\Cache;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class CreateResourceController
{
    private $service;
    private $schema;

    public function __construct(Cache $service, $schema)
    {
        $this->service = $service;
        $this->schema = $schema;
    }

    public function __invoke(Application $app, Request $request)
    {
        $requestJson = $request->getContent() ?: "{}";
        $data = json_decode($requestJson);
        $data->id = $app["uniqid"];

        $this->validate($app, $data);

        $location = $app["url_generator"]->generate($this->schema, array("id" => $data->id));
        if ($this->service->save($location, $data) === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to save resource");
        }

        return JsonResponse::create($data, Response::HTTP_CREATED, array("Location" => $location));
    }

    private function validate(Application $app, $data)
    {
        $schema = $app["json-schema.schema-store"]->get($this->schema);
        $validation = $app["json-schema.validator"]->validate($data, $schema);
        if (!$validation->valid) {
            throw new BadRequestHttpException(json_encode($validation->errors));
        }
    }
}
