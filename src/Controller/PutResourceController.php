<?php

namespace JDesrosiers\Resourceful\Controller;

use Doctrine\Common\Cache\Cache;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class PutResourceController
{
    private $service;
    private $schema;

    public function __construct(Cache $service, $schema)
    {
        $this->service = $service;
        $this->schema = $schema;
    }

    public function __invoke(Application $app, Request $request, $id)
    {
        $requestJson = $request->getContent() ?: "{}";
        $data = json_decode($requestJson);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestHttpException("Invalid JSON: " . json_last_error_msg());
        }

        $this->validate($app, $id, $data);

        $isCreated = !$this->service->contains($request->getRequestUri());
        if ($this->service->save($request->getRequestUri(), $data) === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to save resource");
        }

        return JsonResponse::create($data, $isCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    private function validate(Application $app, $id, $data)
    {
        if ($id !== $data->id) {
            throw new BadRequestHttpException("The `id` in the body must match the `id` in the URI");
        }
        $schema = $app["json-schema.schema-store"]->get($this->schema);
        $validation = $app["json-schema.validator"]->validate($data, $schema);
        if (!$validation->valid) {
            throw new BadRequestHttpException(json_encode($validation->errors));
        }
    }
}
