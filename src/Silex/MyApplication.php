<?php

namespace JDesrosiers\Silex;

use Igorw\Silex\ConfigServiceProvider;
use JDesrosiers\Silex\Provider\ContentNegotiationServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use JDesrosiers\Silex\Provider\JmsSerializerServiceProvider;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

class MyApplication extends Application
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Middleware
        $this->register(new UrlGeneratorServiceProvider());

        $this->register(new JmsSerializerServiceProvider(), array(
            "serializer.cacheDir" => __DIR__ . "/../../cache",
            "serializer.namingStrategy" => "IdenticalProperty",
        ));

        $this->register(new ContentNegotiationServiceProvider(), array(
            "conneg.responseFormats" => array("json"),
            "conneg.requestFormats" => array("json"),
            "conneg.defaultFormat" => "json",
        ));

        $this->register(new CorsServiceProvider());

        // Configuration.  Make sure you register ConfigServiceProvider last.
        $env = getenv("APP_ENV") ?: "prod";
        $this->register(new ConfigServiceProvider(__DIR__ . "/../../config/$env.json"));

        $this->after($this["cors"]);
    }
}
