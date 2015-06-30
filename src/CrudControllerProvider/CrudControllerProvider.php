<?php

namespace JDesrosiers\Resourceful\CrudControllerProvider;

use Doctrine\Common\Cache\Cache;
use JDesrosiers\Resourceful\Controller\CreateResourceController;
use JDesrosiers\Resourceful\Controller\DeleteResourceController;
use JDesrosiers\Resourceful\Controller\GetResourceController;
use JDesrosiers\Resourceful\Controller\PutResourceController;
use JDesrosiers\Resourceful\ResourcefulServiceProvider\AddSchema;
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
        $schema = $app["url_generator"]->generate("schema", array("type" => $this->type));
        $resource = $app["resources_factory"]($schema);

        $app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
        $replacements = array("type" => $this->type, "title" => ucfirst($this->type));
        $app->before(new AddSchema($schema, "crud", $replacements));

        $resource->get("/{id}", new GetResourceController($this->service))->bind($schema);
        $resource->put("/{id}", new PutResourceController($this->service, $schema));
        $resource->delete("/{id}", new DeleteResourceController($this->service));
        $resource->post("/", new CreateResourceController($this->service, $schema));

        return $resource;
    }
}
