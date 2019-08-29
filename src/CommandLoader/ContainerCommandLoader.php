<?php
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
        $this->commands = $commands;
    }

    /** @inheritDoc */
    public function get($name)
    {
        foreach ($this->commands as $index => $command) {
            if ($name === $command::getDefaultName()) {
                if (is_string($command)) {
                    $this->commands[$index] = $this->container->get($command);
                }
                return $this->commands[$index];
            }
        }
        throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
    }

    /** @inheritDoc */
    public function has($name)
    {
        foreach ($this->commands as $command) {
            if ($name === $command::getDefaultName()) {
                return true;
            }
        }
        return false;
    }

    /** @inheritDoc */
    public function getNames()
    {
        $names = [];
        foreach ($this->commands as $command) {
            $names[] = $command::getDefaultName();
        }
        return $names;
    }
}
