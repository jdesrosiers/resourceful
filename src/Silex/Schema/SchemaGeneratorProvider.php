<?php

namespace JDesrosiers\Silex\Schema;

use Silex\Application;
use Silex\ServiceProviderInterface;

class SchemaGeneratorProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        
    }

    public function register(Application $app)
    {
        $app["generateSchema"] = $app->protect(
            function ($schema, $template, array $replacements = array()) use ($app) {
                $genericSchemaJson = str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    file_get_contents($template)
                );

                $app["schemaService"]->put($schema, json_decode($genericSchemaJson));
            }
        );
    }
}
