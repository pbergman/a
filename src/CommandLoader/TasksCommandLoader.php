<?php
declare(strict_types=1);

namespace App\CommandLoader;

use App\CommandBuilder\CommandBuilderInterface;
use App\Plugin\PluginConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

class TasksCommandLoader implements CommandLoaderInterface
{
    private $config;
    private $builder;

    public function __construct(PluginConfig $config, CommandBuilderInterface $builder)
    {
        $this->config = $config;
        $this->builder = $builder;
    }

    public function getAvailableNames() :array
    {
        static $names;

        if (!$names) {
            $names = array_keys($this->config->getTasks());
        }

        return $names;
    }

    /** @inheritDoc */
    public function has($name) :bool
    {
        return in_array($name, $this->getAvailableNames());
    }

    /** @inheritDoc */
    public function getNames() :array
    {
        return $this->getAvailableNames();
    }

    /** @inheritDoc */
    public function get($name) :Command
    {
        return $this->builder->getCommand($name);
    }
}
