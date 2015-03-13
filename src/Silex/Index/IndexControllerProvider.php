<?php

namespace JDesrosiers\Silex\Index;

use Doctrine\Common\Cache\Cache;
use JDesrosiers\Silex\Generic\GetResourceController;
use JDesrosiers\Silex\Schema\AddSchema;
use JDesrosiers\Silex\Schema\DescribedBy;
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

        $schema = $app["url_generator"]->generate("schema", array("type" => "index"));
        $controller->after(new DescribedBy($schema));

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $controller->before(new AddSchema($schema, "index"));

        $controller->get("/", new GetResourceController($this->service))->bind("index");

        return $controller;
    }
}
