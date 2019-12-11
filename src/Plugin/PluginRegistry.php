<?php
declare(strict_types=1);

namespace App\Plugin;

class PluginRegistry implements \IteratorAggregate
{
    private $registry = [];

    public function addPlugin($name, $plugin)
    {
        $this->registry[$name] = $plugin;
    }

    public function getPlugin($name) :?PluginInterface
    {
        return $this->registry[$name] ?? null;
    }

    public function getPluginNames() :array
    {
        return array_keys($this->registry);
    }

    public function getIterator()
    {
        foreach ($this->registry as $key => $plugin) {
            yield $key => $plugin;
        }
    }
}