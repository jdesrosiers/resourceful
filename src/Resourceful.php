<?php

namespace JDesrosiers\Resourceful;

use JDesrosiers\Resourceful\JsonErrorHandler\JsonErrorHandler;
use JDesrosiers\Silex\Provider\ContentNegotiationServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use JDesrosiers\Silex\Provider\JsonSchemaServiceProvider;
use Silex\Application;
use Symfony\Component\Debug\ErrorHandler;

class Resourceful extends Application
{
    public function __construct($config = [])
    {
        parent::__construct($config);
        ErrorHandler::register();

        // JSON/REST application
        $this->register(new ContentNegotiationServiceProvider(), [
            "conneg.responseFormats" => ["json"],
            "conneg.requestFormats" => ["json"],
            "conneg.defaultFormat" => "json",
        ]);
        $this->register(new CorsServiceProvider());

        // JSON Schema application
        $this->register(new JsonSchemaServiceProvider());

        // Error Handling
        $this->error(new JsonErrorHandler($this));
        
        // CreateResourceController
        $this["uniqid"] = function () {
            return uniqid();
        };
    }
}
