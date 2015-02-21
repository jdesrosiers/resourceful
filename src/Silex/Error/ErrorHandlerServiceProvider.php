<?php

namespace JDesrosiers\Silex\Error;

use JDesrosiers\Silex\Schema\AddSchema;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Twig_Loader_Filesystem;

class ErrorHandlerServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        
    }

    public function register(Application $app)
    {
        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $app->before(new AddSchema("error", "error"));

        $app->error(function (\Exception $e, $code) use ($app) {
            $error = array("code" => $e->getCode(), "message" => $e->getMessage());
            if ($app["debug"]) {
                $error["trace"] = $e->getTraceAsString();
            }

            $app["json-schema.describedBy"] = "/schema/error";
            return $app->json($error);
        });
    }
}
