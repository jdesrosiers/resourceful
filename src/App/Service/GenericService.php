<?php

namespace JDesrosiers\App\Service;

interface GenericService
{
    function fetch($id);
    function contains($id);
    function save($id, $object);
    function delete($id);
}
