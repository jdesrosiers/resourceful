<?php

namespace JDesrosiers\App\Service;

class SessionService implements GenericService
{
    private $type;

    public function __construct($type)
    {
        $this->type = $type;
        if (!array_key_exists($this->type, $_SESSION)) {
            $_SESSION[$this->type] = array();
        }
    }

    public function query()
    {
        return $_SESSION[$this->type];
    }

    public function get($id)
    {
        return $_SESSION[$this->type][$id];
    }

    public function put($id, $object)
    {
        $exists = array_key_exists($id, $_SESSION[$this->type]);
        $_SESSION[$this->type][$id] = $object;

        return $exists ? self::UPDATED : self::CREATED;
    }

    public function delete($id)
    {
        if (array_key_exists($id, $_SESSION[$this->type])) {
            unset($_SESSION[$this->type][$id]);
            return self::DELETED;
        } else {
            return self::NO_SUCH_ITEM;
        }
    }
}
