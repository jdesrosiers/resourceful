<?php

namespace JDesrosiers\Silex\Schema;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AddSchema
{
    private $type;
    private $replacements;

    public function __construct($type, $replacements = array())
    {
        $this->type = $type;
        $this->replacements = $replacements;
    }

    public function __invoke(Request $request, Application $app)
    {
        if (!$app["schemaService"]->contains($this->type)) {
            $app["schemaService"]->save(
                $this->type,
                json_decode($app["twig"]->render("generic.json.twig", $this->replacements))
            );
        }

        $app["json-schema.schema-store"]->add("/schema/$this->type", $app["schemaService"]->fetch($this->type));
    }
}
