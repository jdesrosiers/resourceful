<?php

namespace JDesrosiers\Silex\JsonSchema;

use Silex\Application;

class DescribedByError
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function __invoke(\Exception $e, $code)
    {
        if ($this->app->offsetExists("json-schema.errorSchema")) {
            $this->app["json-schema.describedBy"] = $this->app["json-schema.errorSchema"];
        } else {
            unset($this->app["json-schema.describedBy"]);
        }
    }
}
