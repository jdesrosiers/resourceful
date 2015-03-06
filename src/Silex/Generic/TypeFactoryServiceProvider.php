<?php

namespace JDesrosiers\Silex\Generic;

use Silex\Application;
use Silex\ServiceProviderInterface;

class TypeFactoryServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        
    }

    public function register(Application $app)
    {
        $app["typeFactory"] = $app->protect(new TypeFactory($app));
    }
}
