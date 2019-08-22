<?php
declare(strict_types=1);

namespace App;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private $registry;

    public function __construct(...$objects)
    {
        foreach ($objects as $object) {
            $this->registry[get_class($object)] = $object;
        }
    }

    /** @inheritDoc\ */
     public function get($id)
     {
        if (isset($this->registry[$id])) {
            return $this->registry[$id];
        }

        $file = sprintf('./src/Container/%s.php', str_replace('\\', '', $id));

        if (file_exists($file)) {
            return $this->registry[$id] = include_once $file;
        }

        return $this->registry[$id] = new $id();
     }

    /** @inheritDoc\ */
    public function has($id)
    {
        return true;
    }
}
