<?php
declare(strict_types=1);

namespace App\CommandLoader;

use App\AppConfig;
use App\CommandBuilder\CommandBuilderInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

class TasksCommandLoader implements CommandLoaderInterface
{
    private $config;
    private $builder;

    public function __construct(AppConfig $config, CommandBuilderInterface $builder)
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
    public function has($name)
    {
        return in_array($name, $this->getAvailableNames());
    }

    /** @inheritDoc */
    public function getNames()
    {
        return $this->getAvailableNames();
    }

    /** @inheritDoc */
    public function get($name)
    {
        return $this->builder->getCommand($name);
    }
}
