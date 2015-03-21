<?php

namespace JDesrosiers\Silex;

use JDesrosiers\Silex\Error\ErrorSchemaGenerator;
use JDesrosiers\Silex\Error\JsonErrorHandler;
use JDesrosiers\Silex\JsonSchema\DescribedByError;
use JDesrosiers\Silex\JsonSchema\JsonSchemaServiceProvider;
use JDesrosiers\Silex\Provider\ContentNegotiationServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
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
        $this->register(new JsonSchemaServiceProvider(), array(
            "json-schema.errorSchema" => new ErrorSchemaGenerator(),
        ));
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
