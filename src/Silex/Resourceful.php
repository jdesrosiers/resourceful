<?php

namespace JDesrosiers\Silex;

use JDesrosiers\Doctrine\Cache\FileCache;
use JDesrosiers\Silex\Error\ErrorHandlerServiceProvider;
use JDesrosiers\Silex\Provider\ContentNegotiationServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use JDesrosiers\Silex\Schema\JsonSchemaServiceProvider;
use JDesrosiers\Silex\Schema\SchemaControllerProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;

class Resourceful extends Application
{
    public function __construct($config = array())
    {
        parent::__construct($config);
        ErrorHandler::register();

        // Middleware
        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new TwigServiceProvider());
        $this->register(new ContentNegotiationServiceProvider(), array(
            "conneg.responseFormats" => array("json"),
            "conneg.requestFormats" => array("json"),
            "conneg.defaultFormat" => "json",
        ));
        $this->register(new CorsServiceProvider());
        $this->register(new JsonSchemaServiceProvider());
        $this["uniqid"] = function () {
            return uniqid();
        };

        // Supporting Controllers
        $this["schemaService"] = new FileCache($this["rootPath"]);
        $this->mount("/schema", new SchemaControllerProvider($this["schemaService"]));

        // Error Handler
        $this->register(new ErrorHandlerServiceProvider());
    }
}
