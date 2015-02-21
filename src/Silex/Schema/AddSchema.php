<?php

namespace JDesrosiers\Silex\Schema;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AddSchema
{
    private $type;
    private $template;
    private $replacements;

    public function __construct($type, $template, $replacements = array())
    {
        $this->type = $type;
        $this->template = $template;
        $this->replacements = $replacements;
    }

    public function __invoke(Request $request, Application $app)
    {
        if (!$app["schemaService"]->contains("/schema/$this->type")) {
            $app["schemaService"]->save(
                "/schema/$this->type",
                json_decode($app["twig"]->render("$this->template.json.twig", $this->replacements))
            );
        }

        $app["json-schema.schema-store"]->add(
            "/schema/$this->type",
            $app["schemaService"]->fetch("/schema/$this->type")
        );
    }
}
