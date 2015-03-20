<?php

namespace JDesrosiers\Silex\Index;

use Doctrine\Common\Cache\Cache;
use JDesrosiers\Silex\Controller\GetResourceController;
use JDesrosiers\Silex\Schema\AddSchema;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
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

        $resource->before(function (Request $request, Application $app) {
            $index = $app["url_generator"]->generate("index");
            if (!$this->service->contains($index)) {
                $this->service->save($index, json_decode($app["twig"]->render("default.json.twig")));
            }
        });

        $resource->get("/", new GetResourceController($this->service))->bind("index");

        return $resource;
    }
}
