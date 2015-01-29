<?php

namespace JDesrosiers\Silex\Error;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig_Loader_Filesystem;

class ErrorHandlerServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        
    }

    public function register(Application $app)
    {
        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));

        $app->before(function (Request $request, Application $app) {
            if (!$app["schemaService"]->contains("error")) {
                $app["schemaService"]->save("error", json_decode($app["twig"]->render("error.json.twig")));
            }

            $app["schema-store"]->add("/schema/error", $app["schemaService"]->fetch("error"));
        });

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
