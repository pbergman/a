<?php

namespace App\Command;

use App\AppConfig;
use App\Config\ConfigTreeBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandLoader implements CommandLoaderInterface
{
    /** @var AppConfig  */
    private $appConfig;
    private $builder;

    private $staticCommands = [
        'config:dump:reference'
    ];


    public function __construct(AppConfig $appConfig, ConfigTreeBuilder $builder)
    {
        $this->appConfig = $appConfig;
        $this->builder = $builder;
    }

    /**
     * Loads a command.
     *
     * @inheritDoc
     */
    public function get($name)
    {
        switch ($name) {
            case 'config:dump:reference':
                return new ConfigDumpReferenceCommand($this->appConfig, $this->builder);
                break;
            default:
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
    }

    public function getAvailableNames() :array
    {
        static $names;

        if (!$names) {
            $names = array_merge(array_keys($this->appConfig->getTasks()), $this->staticCommands);
        }

        return $names;
    }

    /**
     * @inheritDoc
     */
    public function has($name)
    {
        return in_array($name, $this->getAvailableNames());
    }

    /**
     * @inheritDoc
     */
    public function getNames()
    {
        return $this->getAvailableNames();
    }
}