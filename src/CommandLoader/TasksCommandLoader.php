<?php
declare(strict_types=1);

namespace App\CommandLoader;

use App\Exec\ExecInterface;
use App\Plugin\PluginConfig;
use App\ShellScript\ShellScriptFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TasksCommandLoader implements CommandLoaderInterface
{
    /** @var PluginConfig  */
    private $config;
    /** @var array  */
    private $commands = [];
    /** @var ShellScriptFactoryInterface  */
    private $scriptBuilder;
    /** @var ExecInterface  */
    private $exec;

    public function __construct(PluginConfig $config, ShellScriptFactoryInterface $scriptBuilder, ExecInterface $exec)
    {
        $this->config = $config;
        $this->scriptBuilder = $scriptBuilder;
        $this->exec = $exec;
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
        if (!isset($this->commands[$name])) {
            $this->commands[$name] = $this->newCommand($name);
        }

        return $this->commands[$name];
    }

    private function newCommand(string $name) :Command
    {
        $tasks = $this->config->getTasks();

        if (!isset($tasks[$name])) {
            throw new CommandNotFoundException('Command "' . $name . '" does not exist.');
        }

        $task = $tasks[$name];
        $cmd = new Command($name);

        if (!empty($task['help'])) {
            $cmd->setHelp($task['help']);
        }

        if (!empty($task['description'])) {
            $cmd->setDescription($task['description']);
        }

        foreach ($task['opts'] as $optName => $opt) {
            $cmd->addOption($optName, $opt['shortcut'], $opt['mode'], $opt['description'], $opt['default']);
        }

        foreach ($task['args'] as $argName => $arg) {
            $cmd->addArgument($argName, $arg['mode'], $arg['description'], $arg['default']);
        }

        if ($task['hidden']) {
            $cmd->setHidden(true);
        }

        $cmd->addOption('dump', 'd', InputOption::VALUE_NONE, 'Dump the script instead of executing');

        $cmd->setCode(function(InputInterface $input, OutputInterface $output) use ($name) {
            $cxt = ['input' => $input, 'output' => $output];

            if ($input->getOption('dump')) {
                $this->scriptBuilder->create(STDOUT, $name, $this->config, $cxt);
            } else {
                try {
                    $script = fopen('php://temp', 'wb+');
                    $this->scriptBuilder->create($script, $name, $this->config, $cxt);
                    return $this->exec->exec($script, $this->config->getEnvs($name));
                } finally {
                    fclose($script);
                }
            }
            return 0;
        });

        return $cmd;
    }
}
