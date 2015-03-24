<?php

namespace JDesrosiers\Resourceful;

use JDesrosiers\Resourceful\DescribedBy\DescribedByError;
use JDesrosiers\Resourceful\DescribedBy\ResourcesFactory;
use JDesrosiers\Resourceful\Error\JsonErrorHandler;
use JDesrosiers\Resourceful\JsonSchema\JsonSchemaServiceProvider;
use JDesrosiers\Resourceful\Provider\ContentNegotiationServiceProvider;
use JDesrosiers\Resourceful\Provider\CorsServiceProvider;
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

        // JSON/REST application
        $this->register(new ContentNegotiationServiceProvider(), array(
            "conneg.responseFormats" => array("json"),
            "conneg.requestFormats" => array("json"),
            "conneg.defaultFormat" => "json",
        ));
        $this->register(new CorsServiceProvider());

        // JSON Schema application
        $this->register(new JsonSchemaServiceProvider());
        $this["resources_factory"] = $this->protect(new ResourcesFactory($this));

        // Error Handling
        $this->error(new DescribedByError($this));
        $this->error(new JsonErrorHandler($this));

        // Schema generation
        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new TwigServiceProvider());

        // CreateResourceController
        $this["uniqid"] = function () {
            return uniqid();
        };
    }
}
