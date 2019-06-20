<?php

namespace App\Console;

use App\AppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandLoader implements CommandLoaderInterface
{
    /** @var AppConfig  */
    private $appConfig;

    public function __construct(AppConfig $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    /**
     * Loads a command.
     *
     * @inheritDoc
     */
    public function get($name)
    {
        return new class($name, $this->appConfig->getTasks()[$name]) extends Command{
            private $task;
            public function __construct(string $name, array $cnf)
            {
                $this->task = $cnf;
                parent::__construct($name);
            }
            protected function configure()
            {
                if (!empty($this->task['help'])) {
                    $this->setHelp($this->task['help']);
                }
                if (!empty($this->task['description'])) {
                    $this->setDescription($this->task['description']);
                }
                foreach ($this->task['opts'] as $name => $opt) {
                    $this->addOption($name, $opt['shortcut'], $opt['mode'], $opt['description'], $opt['default']);
                }
                foreach ($this->task['args'] as $name => $arg) {
                    $this->addArgument($name, $arg['mode'], $arg['description'], $arg['default']);
                }
                if ($this->task['hidden']) {
                    $this->setHidden(true);
                }
            }
            protected function execute(InputInterface $input, OutputInterface $output)
            {
                var_dump($input->getArguments(), $input->getOptions(), $this->task);
            }
        };
    }

    /**
     * @inheritDoc
     */
    public function has($name)
    {
        return isset($this->appConfig->getTasks()[$name]);
    }

    /**
     * @inheritDoc
     */
    public function getNames()
    {
        return array_keys($this->appConfig->getTasks());
    }
}