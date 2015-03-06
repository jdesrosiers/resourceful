<?php

namespace JDesrosiers\Silex\Generic;

use Doctrine\Common\Cache\Cache;

class TypeContext
{
    private $service;
    private $schema;

    public function __construct(Cache $service, $schema)
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
