<?php

namespace JDesrosiers\Silex;

use Igorw\Silex\ConfigServiceProvider;
use JDesrosiers\Silex\Generic\GenericServiceProvider;
use JDesrosiers\Silex\Index\IndexControllerProvider;
use JDesrosiers\Silex\Provider\ContentNegotiationServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use JDesrosiers\Silex\Schema\JsonSchemaServiceProvider;
use JDesrosiers\Silex\Schema\SchemaControllerProvider;
use Silex\Application;
use Silex\Provider\UrlGeneratorServiceProvider;

class MyApplication extends Application
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Middleware
        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new ContentNegotiationServiceProvider());
        $this->register(new CorsServiceProvider());

        // App specific
        $this->register(new GenericServiceProvider());
        $this->register(new JsonSchemaServiceProvider());

        // Configuration.  Make sure you register ConfigServiceProvider last.
        $env = getenv("APP_ENV") ?: "prod";
        $this->register(new ConfigServiceProvider(__DIR__ . "/../../config/$env.json", array(
            'rootPath' => __DIR__ . '/../..',
        )));

        // Add standard controllers
        $this->mount("/schema", new SchemaControllerProvider());
        $this->mount("/", new IndexControllerProvider());

        // Initialize CORS support
        $this->after($this["cors"]);
    }
}
