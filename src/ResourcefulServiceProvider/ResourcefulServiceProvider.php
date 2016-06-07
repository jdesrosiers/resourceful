<?php

namespace JDesrosiers\Resourceful\ResourcefulServiceProvider;

use JDesrosiers\Resourceful\JsonErrorHandler\JsonErrorHandler;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Twig_Loader_Filesystem;

class ResourcefulServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function boot(Application $app)
    {
        // Error Handling
        $schema = $app["url_generator"]->generate("schema", array("type" => "error"));

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $app->before(new AddSchema($schema, "error"));
    }

    public function register(Container $app)
    {
        $app["resources_factory"] = $app->protect(new ResourcesFactory($app));

        // CreateResourceController
        $app["uniqid"] = function () {
            return uniqid();
        };

        // Error Handling
        $app->error(function (\Exception $e, $code) use ($app) {
            $app["json-schema.describedBy"] = $app["url_generator"]->generate("schema", array("type" => "error"));
        });
        $app->error(new JsonErrorHandler($app));
    }
}
