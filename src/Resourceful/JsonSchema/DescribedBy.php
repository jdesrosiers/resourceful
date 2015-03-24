<?php

namespace JDesrosiers\Resourceful\JsonSchema;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class DescribedBy
{
    public function __invoke(Request $request, Response $response, Application $app)
    {
        if ($app->offsetExists("json-schema.describedBy") && !$response->isEmpty()) {
            if ($app["json-schema.correlationMechanism"] === "profile") {
                $contentType = $response->headers->get("Content-Type");
                $response->headers->set("Content-Type", "$contentType; profile=\"{$app["json-schema.describedBy"]}\"");
            } elseif ($app["json-schema.correlationMechanism"] === "link") {
                $response->headers->set("Link", "<{$app["json-schema.describedBy"]}>; rel=\"describedBy\"", false);
            } else {
                $errorMessage = "json-schema.correlationMechanism must be either \"profile\" or \"link\"";
                throw new ServiceUnavailableHttpException(null, $errorMessage);
            }
        }
    }
}
