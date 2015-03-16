<?php

namespace JDesrosiers\Silex;

use JDesrosiers\Silex\Generic\ResourcesFactoryServiceProvider;
use JDesrosiers\Silex\Provider\ContentNegotiationServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use JDesrosiers\Silex\Schema\DescribedBy;
use JDesrosiers\Silex\Schema\JsonSchemaServiceProvider;
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
        $this->register(new ResourcesFactoryServiceProvider());
        $this->register(new JsonSchemaServiceProvider());
        $this["uniqid"] = function () {
            return uniqid();
        };
        $this->after(new DescribedBy());
    }
}
