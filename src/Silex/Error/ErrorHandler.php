<?php

namespace JDesrosiers\Silex\Error;

use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorHandler
{
    private $debug;

    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    public function __invoke(\Exception $e, $code)
    {
        $error = array("code" => $e->getCode(), "message" => $e->getMessage());
        if ($this->debug) {
            $error["trace"] = $e->getTraceAsString();
        }

        return JsonResponse::create($error);
    }
}
