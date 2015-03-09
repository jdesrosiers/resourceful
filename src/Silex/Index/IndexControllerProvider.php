<?php

namespace JDesrosiers\Silex\Index;

use Doctrine\Common\Cache\Cache;
use JDesrosiers\Silex\Generic\GetResourceController;
use JDesrosiers\Silex\Generic\TypeContext;
use JDesrosiers\Silex\Schema\AddSchema;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Twig_Loader_Filesystem;

class IndexControllerProvider implements ControllerProviderInterface
{
    private $service;

    public function __construct(Cache $service)
    {
        $this->service = $service;
    }

    public function connect(Application $app)
    {
        $controller = $app["controllers_factory"];

        $type = new TypeContext($this->service, "/schema/index");
        $controller->get("/", new GetResourceController($type))->bind("index");

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $controller->before(new AddSchema($type->schema, "index"));

        return $controller;
    }
}
