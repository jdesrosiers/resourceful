<?php

namespace JDesrosiers\Silex\Schema;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AddSchema
{
    private $schema;
    private $template;
    private $replacements;

    public function __construct($schema, $template, $replacements = array())
    {
        $this->schema = $schema;
        $this->template = $template;
        $this->replacements = $replacements;
    }

    public function __invoke(Request $request, Application $app)
    {
        if (!$app["schemaService"]->contains($this->schema)) {
            $app["schemaService"]->save(
                $this->schema,
                json_decode($app["twig"]->render("$this->template.json.twig", $this->replacements))
            );
        }
    }
}
