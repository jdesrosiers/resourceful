<?php

namespace JDesrosiers\Silex\Foo;

use JDesrosiers\App\Foo\FooServiceStatic;
use Silex\Application;
use Silex\ServiceProviderInterface;

class FooServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app["foo"] = function () {
            return new FooServiceStatic();
        };
    }

    public function boot(Application $app)
    {

    }
}
