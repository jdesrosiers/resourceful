<?php

namespace JDesrosiers\Silex\Error;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorHandler
{
    private $app;
    private $schema;

    public function __construct(Application $app, $schema = null)
    {
        $this->app = $app;
        $this->schema = $schema;
    }

    public function __invoke(\Exception $e, $code)
    {
        $error = array("code" => $e->getCode(), "message" => $e->getMessage());
        if ($this->app["debug"]) {
            $error["trace"] = $e->getTraceAsString();
        }

        $this->app["json-schema.describedBy"] = $this->schema;
        return JsonResponse::create($error);
    }
}
