<?php

namespace JDesrosiers\Silex\Generic;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class PutResourceController
{
    private $service;
    private $schema;

    public function __construct(TypeContext $type)
    {
        $this->service = $type->service;
        $this->schema = $type->schema;
    }

    public function __invoke(Application $app, Request $request, $id)
    {
        $requestJson = $request->getContent() ?: "{}";
        $data = json_decode($requestJson);

        if ($id !== $data->id) {
            throw new BadRequestHttpException("The `id` in the body must match the `id` in the URI");
        }
        $schema = $app["json-schema.schema-store"]->get($this->schema);
        $validation = $app["json-schema.validator"]->validate($data, $schema);
        if (!$validation->valid) {
            throw new BadRequestHttpException(json_encode($validation->errors));
        }

        $isCreated = !$this->service->contains($request->getRequestUri());

        $success = $this->service->save($request->getRequestUri(), $data);
        if ($success === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to save resource");
        }

        $app["json-schema.describedBy"] = $this->schema;
        return $app->json($data, $isCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
    }
}
