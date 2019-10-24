<?php
declare(strict_types=1);

namespace App\CommandLoader;

use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class CommandLoader implements CommandLoaderInterface
{
    private $loaders;

    public function __construct(CommandLoaderInterface ...$loader)
    {
        $this->loaders = $loader;
    }

    /** @inheritDoc */
    public function get($name)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->has($name)) {
                return $loader->get($name);
            }
        }

        throw new CommandNotFoundException('Command "' . $name . '" does not exist.');
    }

    /** @inheritDoc */
    public function has($name)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->has($name)) {
                return true;
            }
        }
        return false;
    }

    /** @inheritDoc */
    public function getNames()
    {
        $names = [];

        foreach ($this->loaders as $loader) {
            foreach($loader->getNames() as $name) {
                $names[] = $name;
            }
        }

        return $names;
    }
}