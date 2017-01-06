<?php

namespace JDesrosiers\Resourceful\ResourcefulServiceProvider;

use JDesrosiers\Resourceful\FileCache\FileCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Twig_Loader_Filesystem;

class ResourcefulServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    const ERROR_HANDLER_PRIORITY = 0;

    public function boot(Application $app)
    {
        // Error Handling
        $schema = $app["url_generator"]->generate("schema", ["type" => "error"]);

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $app->before(new AddSchema($schema, "error"));

        $app->error(function (\Exception $e, $code) use ($app, $schema) {
            $app["json-schema.describedBy"] = $schema;
        }, self::ERROR_HANDLER_PRIORITY);
    }

    public function register(Container $app)
    {
        $app["resources_factory"] = $app->protect(new ResourcesFactory($app));
        $app["resourceful.schemaStore"] = function (Container $app) {
            return new FileCache($app["resourceful.schema-dir"]);
        };
    }
}
