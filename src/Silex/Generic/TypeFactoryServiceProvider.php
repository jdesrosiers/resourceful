<?php

namespace JDesrosiers\Silex\Generic;

use JDesrosiers\Silex\Schema\AddSchema;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Twig_Loader_Filesystem;

class TypeFactoryServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        
    }

    public function register(Application $app)
    {
        $app["typeFactory"] = $app->protect(function ($type) use ($app) {
            $controller = $app["controllers_factory"];

            $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
            $replacements = array("type" => $type, "title" => ucfirst($type));
            $controller->before(new AddSchema($type, "generic", $replacements));

            $context = new TypeContext($app["data"], "/schema/$type");

            return array($context, $controller);
        });
    }
}
