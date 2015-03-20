<?php

namespace JDesrosiers\Silex\Error;

use JDesrosiers\Silex\Schema\AddSchema;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Twig_Loader_Filesystem;

class JsonErrorHandlerServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {

    }

    public function register(Application $app)
    {
        $schema = $app["url_generator"]->generate("schema", array("type" => "error"));

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $app->before(new AddSchema($schema, "error"));

        $app->error(new JsonErrorHandler($app["debug"]));
        $app->error(function () use ($app, $schema) {
            $app["json-schema.describedBy"] = $schema;
        });
    }
}
