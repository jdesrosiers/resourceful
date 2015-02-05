<?php

namespace JDesrosiers\Silex\Generic;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SetProfile
{
    private $schema;

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    public function __invoke(Request $request, Response $response)
    {
        if ($response->getContent() !== "" && $response->headers->get("Content-Type") === "application/json") {
            $response->headers->set("Content-Type", "application/json; profile=\"$this->schema\"");
        }
    }
}
