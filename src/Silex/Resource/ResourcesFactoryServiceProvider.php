<?php

namespace JDesrosiers\Silex\Resource;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ResourcesFactoryServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        
    }

    public function register(Application $app)
    {
        $app["resources_factory"] = $app->protect(new ResourcesFactory($app));
    }
}
