<?php

namespace JDesrosiers\Silex\Generic;

use JDesrosiers\Silex\Generic\TypeContext;
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

    public function __construct(TypeContext $type)
    {
        $this->service = $type->service;
        $this->schema = $type->schema;
    }

    public function __invoke(Application $app, Request $request)
    {
        $requestJson = $request->getContent() ?: "{}";
        $data = json_decode($requestJson);
        $data->id = $app["uniqid"];

        $schema = $app["json-schema.schema-store"]->get($this->schema);
        $validation = $app["json-schema.validator"]->validate($data, $schema);
        if (!$validation->valid) {
            throw new BadRequestHttpException(json_encode($validation->errors));
        }

        $location = $app["url_generator"]->generate($this->schema, array("id" => $data->id));
        $success = $this->service->save($location, $data);
        if ($success === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to save resource");
        }

        $app["json-schema.describedBy"] = $this->schema;
        return JsonResponse::create($data, Response::HTTP_CREATED, array("Location" => $location));
    }
}
