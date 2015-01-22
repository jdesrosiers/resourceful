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

    public function fetch($id)
    {
        if (!$this->contains($id)) {
            return false;
        }

        return json_decode(file_get_contents("$this->location/$id.json"));
    }

    public function contains($id)
    {
        return $this->filesystem->exists("$this->location/$id.json");
    }

    public function save($id, $object)
    {
        $json = json_encode($object, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->filesystem->dumpFile("$this->location/$id.json", $json);
    }

    public function delete($id)
    {
        $this->filesystem->remove("$this->location/$id.json");
    }
}
