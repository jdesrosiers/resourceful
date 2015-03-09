<?php

namespace JDesrosiers\Silex\Generic;

use Doctrine\Common\Cache\Cache;
use JDesrosiers\Silex\Schema\AddSchema;
use Silex\Application;
use Twig_Loader_Filesystem;

class TypeFactory
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function __invoke(Cache $service, $type)
    {
        $controller = $this->app["controllers_factory"];

        $context = new TypeContext($service, "/schema/$type");

        $this->app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $replacements = array("type" => $type, "title" => ucfirst($type));
        $controller->before(new AddSchema($context->schema, "generic", $replacements));

        return array($context, $controller);
    }
}
