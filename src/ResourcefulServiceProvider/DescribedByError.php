<?php

namespace JDesrosiers\Resourceful\ResourcefulServiceProvider;

use Silex\Application;
use Twig_Loader_Filesystem;

class DescribedByError
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function __invoke(\Exception $e, $code)
    {
        $schema = $this->app["url_generator"]->generate("schema", array("type" => "error"));

        if (!$this->app["schemaService"]->contains($schema)) {
            $this->app["twig.loader"]->addLoader(new Twig_Loader_Filesystem(__DIR__ . "/templates"));
            $this->app["schemaService"]->save($schema, json_decode($this->app["twig"]->render("error.json.twig")));
        }

        $this->app["json-schema.describedBy"] = $schema;
    }
}
