<?php

namespace JDesrosiers\App\Service;

interface GenericService
{
    const FAILURE = -1;
    const SUCCESS = 0;
    const CREATED = 1;
    const UPDATED = 2;
    const DELETED = 1;
    const NO_SUCH_ITEM = 2;

    function get($id);
    function has($id);
    function put($id, $object);
    function delete($id);
}
