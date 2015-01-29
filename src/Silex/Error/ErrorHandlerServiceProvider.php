<?php

namespace JDesrosiers\Silex\Error;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ErrorHandlerServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        
    }

    public function register(Application $app)
    {
        $app->error(function (\Exception $e, $code) use ($app) {
            $error = array(
                "code" => $e->getCode(),
                "message" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            );

            $response = $app->json($error);
            $response->headers->set("Content-Type", "application/json; profile=\"/schema/error\"");

            return $response;
        });
    }
}
