<?php

namespace JDesrosiers\App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileService implements GenericService
{
    private $filesystem;
    private $finder;
    private $location;

    public function __construct(Filesystem $filesystem, Finder $finder, $location)
    {
        $this->filesystem = $filesystem;
        $this->finder = $finder;
        $this->location = $location;

        if (!$this->filesystem->exists($this->location)) {
            $this->filesystem->mkdir($this->location);
        }
    }

    public function query()
    {
        $objects = array();

        $this->finder->files()->in($this->location);

        foreach ($this->finder as $file) {
            $objects[] = json_decode($file->getContents());
        }

        return $objects;
    }

    public function get($id)
    {
        $this->finder->files()->in($this->location)->name("$id.json");
        foreach ($this->finder as $file) {
            return json_decode($file->getContents());
        }

        return null;
    }

    public function put($id, $object)
    {
        $exists = $this->filesystem->exists("$this->location/$id.json");
        $this->filesystem->dumpFile("$this->location/$id.json", json_encode($object));

        return $exists ? self::UPDATED : self::CREATED;
    }

    public function delete($id)
    {
        if ($this->filesystem->exists("$this->location/$id.json")) {
            unlink("$this->location/$id.json");
            return self::DELETED;
        } else {
            return self::NO_SUCH_ITEM;
        }
    }
}
