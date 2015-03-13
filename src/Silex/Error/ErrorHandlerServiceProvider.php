<?php

namespace JDesrosiers\Silex\Error;

use JDesrosiers\Silex\Schema\AddSchema;
use JDesrosiers\Silex\Schema\DescribedByError;
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
        $schema = $app["url_generator"]->generate("schema", array("type" => "error"));
        $app->after(new DescribedByError($schema));

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $app->before(new AddSchema($schema, "error"));

        $app->error(new ErrorHandler($app["debug"]));
    }
}
