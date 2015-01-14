<?php

namespace JDesrosiers\Silex\Generic;

use JDesrosiers\App\Service\FileService;
use Silex\Application;
use Silex\ServiceProviderInterface;

class GenericServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app["genericService.uniqid"] = function () {
            return uniqid();
        };

        $app["genericService.file"] = $app->protect(function ($namespace, $storagePath = null) use ($app) {
            $storagePath = $storagePath ?: $app["genericService.storagePath"];
            return new FileService("$storagePath/$namespace");
        });
    }

    public function boot(Application $app)
    {

    }
}
