<?php

namespace JDesrosiers\Silex\Generic;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class DeleteResourceController
{
    private $service;

    public function __construct(TypeContext $type)
    {
        $this->service = $type->service;
    }

    public function __invoke(Request $request)
    {
        $success = $this->service->delete($request->getRequestURI());
        if ($success === false) {
            throw new ServiceUnavailableHttpException(null, "Failed to delete resource");
        }

        return Response::create("", Response::HTTP_NO_CONTENT);
    }
}
