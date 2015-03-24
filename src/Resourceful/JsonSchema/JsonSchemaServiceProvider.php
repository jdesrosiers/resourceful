<?php

namespace JDesrosiers\Resourceful\JsonSchema;

use SchemaStore;
use Silex\Application;
use Silex\ServiceProviderInterface;

class JsonSchemaServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        $app->after(new DescribedBy());
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
    }
}
