<?php

namespace JDesrosiers\Silex\Error;

use Silex\Application;

class ErrorHandler
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function __invoke(\Exception $e, $code)
    {
        $error = array("code" => $e->getCode(), "message" => $e->getMessage());
        if ($this->app["debug"]) {
            $error["trace"] = $e->getTraceAsString();
        }

        $this->app["json-schema.describedBy"] = "/schema/error";
        return $this->app->json($error);
    }
}
