<?php
declare(strict_types=1);

namespace App\CommandLoader;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class ContainerCommandLoader implements CommandLoaderInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var Command[]|string[] */
    private $commands;

    public function __construct(ContainerInterface $container, string ...$commands)
    {
        $this->container = $container;

        foreach ($commands as $command) {
            $this->commands[$command::getDefaultName()] = $command;
        }
    }

    /** @inheritDoc */
    public function get($name) :Command
    {
        if (!$this->has($name)) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        if (is_string($this->commands[$name])) {
            $this->commands[$name] = $this->container->get($this->commands[$name]);
        }

        return $this->commands[$name];
    }

    /** @inheritDoc */
    public function has($name) :bool
    {
        return isset($this->commands[$name]);
    }

    /** @inheritDoc */
    public function getNames() :array
    {
        return empty($this->commands) ? [] : array_keys($this->commands);
    }
}
