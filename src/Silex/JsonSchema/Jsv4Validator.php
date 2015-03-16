<?php

namespace JDesrosiers\Silex\JsonSchema;

use Jsv4;

class Jsv4Validator
{
    public function validate($data, $schema)
    {
        return Jsv4::validate($data, $schema);
    }
}
