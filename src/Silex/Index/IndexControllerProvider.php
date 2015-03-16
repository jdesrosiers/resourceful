<?php

namespace JDesrosiers\Silex\Index;

use Doctrine\Common\Cache\Cache;
use JDesrosiers\Silex\Crud\GetResourceController;
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
        $schema = $app["url_generator"]->generate("schema", array("type" => "index"));
        $resource = $app["resources_factory"]($schema);

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $resource->before(new AddSchema($schema, "index"));

        $resource->get("/", new GetResourceController($this->service))->bind("index");

        return $resource;
    }
}
