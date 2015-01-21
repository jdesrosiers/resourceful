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
                $find = array_map(function ($string) {
                    return "%$string%";
                }, array_keys($replacements));

                $genericSchemaJson = str_replace(
                    $find,
                    array_values($replacements),
                    file_get_contents($template)
                );

                return json_decode($genericSchemaJson);
            }
        );
    }
}
