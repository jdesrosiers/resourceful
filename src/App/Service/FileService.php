<?php

namespace JDesrosiers\App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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
        $finder = new Finder();
        $finder->files()->in($this->location)->name("$id.json");
        foreach ($finder as $file) {
            return json_decode($file->getContents());
        }

        return null;
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

        return $exists ? self::UPDATED : self::CREATED;
    }

    public function delete($id)
    {
        if ($this->has($id)) {
            unlink("$this->location/$id.json");
            return self::DELETED;
        } else {
            return self::NO_SUCH_ITEM;
        }
    }
}
