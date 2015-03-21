<?php

namespace JDesrosiers\Silex\Error;

use JDesrosiers\Silex\Schema\AddSchema;
use Silex\Application;
use Twig_Loader_Filesystem;

class ErrorSchemaGenerator
{
    public function __invoke(Application $app)
    {
        $schema = $app["url_generator"]->generate("schema", array("type" => "error"));

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $app->before(new AddSchema($schema, "error"));

        return $schema;
    }
}
