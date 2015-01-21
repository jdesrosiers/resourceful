<?php

namespace JDesrosiers\Silex;

use JDesrosiers\Silex\Generic\GenericServiceProvider;
use JDesrosiers\Silex\Index\IndexControllerProvider;
use JDesrosiers\Silex\Provider\ContentNegotiationServiceProvider;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use JDesrosiers\Silex\Schema\JsonSchemaServiceProvider;
use JDesrosiers\Silex\Schema\SchemaControllerProvider;
use JDesrosiers\Silex\Schema\SchemaGeneratorProvider;
use Silex\Application;
use Silex\Provider\UrlGeneratorServiceProvider;

class MyApplication extends Application
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Middleware
        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new ContentNegotiationServiceProvider(), array(
            "conneg.responseFormats" => array("json"),
            "conneg.requestFormats" => array("json"),
            "conneg.defaultFormat" => "json",
        ));
        $this->register(new CorsServiceProvider());

        // App specific
        $this->register(new GenericServiceProvider());
        $this->register(new SchemaGeneratorProvider());
        $this->register(new JsonSchemaServiceProvider());

        // Supporting Controllers
        $this["schemaService"] = $this->share(function (Application $app) {
            return $app["genericService.file"]("schema", $app["rootPath"]);
        });
        $this->mount("/schema", new SchemaControllerProvider());
        $this->mount("/", new IndexControllerProvider());

        // Initialize CORS support
        $this->after($this["cors"]);
    }
}
