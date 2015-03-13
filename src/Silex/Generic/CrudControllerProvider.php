<?php

namespace JDesrosiers\Silex\Generic;

use Doctrine\Common\Cache\Cache;
use JDesrosiers\Silex\Schema\AddSchema;
use JDesrosiers\Silex\Schema\DescribedBy;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Twig_Loader_Filesystem;

class CrudControllerProvider implements ControllerProviderInterface
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

        $schema = $app["url_generator"]->generate("schema", array("type" => $this->type));
        $controller->after(new DescribedBy($schema));

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $replacements = array("type" => $this->type, "title" => ucfirst($this->type));
        $controller->before(new AddSchema($schema, "generic", $replacements));

        $controller->get("/{id}", new GetResourceController($this->service))->bind($schema);
        $controller->put("/{id}", new PutResourceController($this->service, $schema));
        $controller->delete("/{id}", new DeleteResourceController($this->service));
        $controller->post("/", new CreateResourceController($this->service, $schema));

        return $controller;
    }
}
