<?php

namespace JDesrosiers\Silex\Generic;

use Doctrine\Common\Cache\Cache;
use JDesrosiers\Silex\Schema\AddSchema;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Twig_Loader_Filesystem;

class GenericControllerProvider implements ControllerProviderInterface
{
    private $type;
    private $service;

    public function __construct($type, Cache $service)
    {
        $this->type = strtolower($type);
        $this->service = $service;
    }

    public function connect(Application $app)
    {
        $controller = $app["controllers_factory"];

        $controller->get("/{id}", new GetResourceController($this->service, "/schema/$this->type"))
            ->bind("/schema/$this->type");
        $controller->put("/{id}", new PutResourceController($this->service, "/schema/$this->type"));
        $controller->delete("/{id}", new DeleteResourceController($this->service));

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $replacements = array("type" => $this->type, "title" => ucfirst($this->type));
        $controller->before(new AddSchema($this->type, "generic", $replacements));

        return $controller;
    }
}
