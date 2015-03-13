<?php

namespace JDesrosiers\Silex\Schema;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class DescribedByError
{
    private $schema;

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    public function __invoke(Request $request, Response $response, Application $app)
    {
        if (!$response->isOk()) {
            if ($app["json-schema.correlationMechanism"] === "profile") {
                $contentType = $response->headers->get("Content-Type");
                $response->headers->set("Content-Type", "$contentType; profile=\"$this->schema\"");
            } elseif ($app["json-schema.correlationMechanism"] === "link") {
                $response->headers->set("Link", "<$this->schema>; rel=\"describedBy\"", false);
            } else {
                $errorMessage = "json-schema.correlationMechanism must be either \"profile\" or \"link\"";
                throw new ServiceUnavailableHttpException(null, $errorMessage);
            }
        }
    }
}
