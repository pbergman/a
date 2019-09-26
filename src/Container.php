<?php
declare(strict_types=1);

namespace App;

use App\Exception\ContainerException;
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
        if (array_key_exists($id, $this->registry)) {
            return $this->registry[$id];
        }
        $file = sprintf('./src/Container/%s.php', str_replace('\\', '', $id));
        if (file_exists($file)) {
            if (($this->registry[$id] = require_once $file) && !$this->registry[$id] instanceof $id) {
                throw new ContainerException('failed to load \'' . $id . '\' from \'' . $file . '\'');
            }
            return $this->registry[$id];
        }
        return $this->registry[$id] = new $id();
    }

    /** @inheritDoc\ */
    public function has($id)
    {
        return true;
    }
}
