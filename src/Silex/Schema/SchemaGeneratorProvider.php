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
            function ($template, array $replacements = array()) {
                $genericSchemaJson = str_replace(
                    array_keys($replacements),
                    array_values($replacements),
                    file_get_contents($template)
                );

                return json_decode($genericSchemaJson);
            }
        );
    }
}
