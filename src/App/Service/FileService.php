<?php

namespace JDesrosiers\App\Service;

use Symfony\Component\Filesystem\Filesystem;

class FileService implements GenericService
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

    public function get($id)
    {
        if (!$this->has($id)) {
            return array(GenericService::NOT_FOUND, null);
        }

        return array(GenericService::OK, json_decode(file_get_contents("$this->location/$id.json")));
    }

    public function has($id)
    {
        return $this->filesystem->exists("$this->location/$id.json");
    }

    public function put($id, $object)
    {
        $exists = $this->has($id);
        $json = json_encode($object, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->filesystem->dumpFile("$this->location/$id.json", $json);

        return $exists ? self::OK : self::CREATED;
    }

    public function delete($id)
    {
        if (!$this->has($id)) {
            return self::NOT_FOUND;
        }

        $this->filesystem->remove("$this->location/$id.json");
        return self::OK;
    }
}
