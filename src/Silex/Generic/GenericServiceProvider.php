<?php

namespace JDesrosiers\Silex\Generic;

use JDesrosiers\App\Service\FileService;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GenericServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app["genericService.uniqid"] = function () {
            return uniqid();
        };

        $app["genericService.file"] = $app->protect(function ($namespace) use ($app) {
            return new FileService(new Filesystem(), new Finder(), "{$app["genericService.location"]}/$namespace");
        });
    }

    public function boot(Application $app)
    {

    }
}
