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
        $app["schema-store"] = $app->share(function () {
            return new SchemaStore();
        });

        $app["validator"] = $app->share(function () {
            return new Jsv4Validator();
        });
    }
}
