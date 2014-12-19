<?php

namespace JDesrosiers\App\Foo;

class FooServiceStatic implements FooServiceInterface
{
    private $foos;

    public function __construct()
    {
        $foo = new Foo();
        $foo->fooBar = 3;

        $this->foos = array(
            "1" => $foo
        );
    }

    public function get($id)
    {
        return $this->foos[$id];
    }
}
