<?php

namespace JDesrosiers\Doctrine\Cache;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class FileCache implements Cache
{
    private $filesystem;
    private $location;

    public function __construct($location)
    {
        $this->filesystem = new Filesystem();
        $this->location = $location;

        if (!$this->filesystem->exists($this->location)) {
            $this->filesystem->mkdir($this->location);
        }
    }

    public function fetch($id)
    {
        if (!$this->contains($id)) {
            return false;
        }

        $json = file_get_contents("$this->location/$id.json");
        if ($json === false) {
            return false;
        }

        $data = json_decode($json);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $data;
    }

    public function contains($id)
    {
        return $this->filesystem->exists("$this->location/$id.json");
    }

    public function save($id, $data, $lifeTime = null)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return false;
        }

        try {
            $this->filesystem->dumpFile("$this->location/$id.json", $json);
        } catch (IOException $ioe) {
            return false;
        }

        return true;
    }

    public function delete($id)
    {
        try {
            $this->filesystem->remove("$this->location/$id.json");
        } catch (IOException $ioe) {
            return false;
        }

        return true;
    }

    public function getStats()
    {
        return null;
    }
}
