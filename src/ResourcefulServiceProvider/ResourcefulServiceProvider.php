<?php

namespace JDesrosiers\Resourceful\ResourcefulServiceProvider;

use JDesrosiers\Resourceful\JsonErrorHandler\JsonErrorHandler;
use Silex\Application;
use Silex\ServiceProviderInterface;

class ResourcefulServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        
    }

    public function register(Application $app)
    {
        $app["resources_factory"] = $app->protect(new ResourcesFactory($app));

        // CreateResourceController
        $app["uniqid"] = function () {
            return uniqid();
        };

        // Error Handling
        $app->error(new DescribedByError($app));
        $app->error(new JsonErrorHandler($app));
    }
}
