<?php

namespace JDesrosiers\Silex;

use Igorw\Silex\ConfigServiceProvider;
use JDesrosiers\Silex\Generic\GenericServiceProvider;
use JDesrosiers\Silex\Provider\ContentNegotiationServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
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
        $this->register(new GenericServiceProvider());

        // Configuration.  Make sure you register ConfigServiceProvider last.
        $env = getenv("APP_ENV") ?: "prod";
        $this->register(new ConfigServiceProvider(__DIR__ . "/../../config/$env.json", array(
            'rootPath' => __DIR__ . '/../..',
        )));

        $this->after($this["cors"]);
    }
}
