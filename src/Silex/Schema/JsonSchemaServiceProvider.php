<?php

namespace JDesrosiers\Silex\Schema;

use SchemaStore;
use Silex\Application;
use Silex\ServiceProviderInterface;

class JsonSchemaServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        
    }

    public function register(Application $app)
    {
        $app["json-schema.correlationMechanism"] = "profile";

        $app["json-schema.schema-store"] = $app->share(function () {
            return new SchemaStore();
        });

        $app["json-schema.validator"] = $app->share(function () {
            return new Jsv4Validator();
        });

        $app->after(new DescribedByFilter());
    }
}
