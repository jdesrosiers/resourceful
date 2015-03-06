<?php

namespace JDesrosiers\Silex\Generic;

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

    public function __invoke($type)
    {
        $controller = $this->app["controllers_factory"];

        $this->app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $replacements = array("type" => $type, "title" => ucfirst($type));
        $controller->before(new AddSchema($type, "generic", $replacements));

        $context = new TypeContext($this->app["data"], "/schema/$type");

        return array($context, $controller);
    }
}
