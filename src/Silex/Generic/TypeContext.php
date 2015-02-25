<?php

namespace JDesrosiers\Silex\Generic;

class TypeContext
{
    private $service;
    private $schema;

    public function __construct($service, $schema)
    {
        $this->service = $service;
        $this->schema = $schema;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new \RuntimeException("Property `$name` does not exist");
    }
}
