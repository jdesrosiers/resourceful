<?php

namespace JDesrosiers\Silex\Generic;

use Doctrine\Common\Cache\Cache;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class GetResourceController
{
    private $service;
    private $schema;
    private $contentType;

    public function __construct(Cache $service, $schema, $contentType = "application/json")
    {
        $this->service = $service;
        $this->schema = $schema;
        $this->contentType = $contentType;
    }

    public function __invoke(Application $app, Request $request)
    {
        if (!$this->service->contains($request->getRequestUri())) {
            throw new NotFoundHttpException("Not Found");
        }

        $resource = $this->service->fetch($request->getRequestUri());
        if ($resource === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to retrieve resource");
        }

        $app["json-schema.describedBy"] = $this->schema;
        $response = $app->json($resource);
        $response->headers->set("Content-Type", $this->contentType);

        return $response;
    }
}
