<?php

namespace JDesrosiers\App\Service;

interface GenericService
{
    const OK = 200;
    const CREATED = 201;
    const NOT_FOUND = 404;

    function get($id);
    function has($id);
    function put($id, $object);
    function delete($id);
}
