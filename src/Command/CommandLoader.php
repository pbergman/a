<?php
declare(strict_types=1);

namespace App\Command;

use App\AppConfig;
use App\ContainerInterface;
use App\Output\CatOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Twig\Environment;

class CommandLoader implements CommandLoaderInterface
{
    /** @var ContainerInterface  */
    private $container;
    /** @var array|string[]|Command[] */
    private $staticCommands = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->setStaticCommands();
    }

    private function setStaticCommands()
    {
        $this->staticCommands = [
            ConfigDumpReferenceCommand::getDefaultName() => ConfigDumpReferenceCommand::class,
        ];
    }

    /**
     * Loads a command.
     *
     * @inheritDoc
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->staticCommands)) {
            return $this->container->get($this->staticCommands[$name]);
        }

        return new class($name, $this->container->get(AppConfig::class)->getTasks()[$name], $this->container->get(Environment::class)) extends Command {

            private $cnf;
            private $twig;

            public function __construct(string $name, array $cnf, Environment $twig)
            {
                $this->cnf = $cnf;
                $this->twig = $twig;
                parent::__construct($name);
            }

            protected function configure()
            {
                if (!empty($this->cnf['help'])) {
                    $this->setHelp($this->cnf['help']);
                }
                if (!empty($this->cnf['description'])) {
                    $this->setDescription($this->cnf['description']);
                }
                foreach ($this->cnf['opts'] as $name => $opt) {
                    $this->addOption($name, $opt['shortcut'], $opt['mode'], $opt['description'], $opt['default']);
                }
                foreach ($this->cnf['args'] as $name => $arg) {
                    $this->addArgument($name, $arg['mode'], $arg['description'], $arg['default']);
                }
                if ($this->cnf['hidden']) {
                    $this->setHidden(true);
                }
            }

            protected function execute(InputInterface $input, OutputInterface $output)
            {

//                $writer = new CatOutput();
//                $out = $writer->write('/bin/bash -ex', $this->getName(), $this->cnf);

                foreach (['pre', 'exec', 'post'] as $group) {
                    foreach ($this->cnf[$group] as $index => $line) {
                        $name = $this->getName() . '.' . $group . '[' . $index . ']';
                        $output->writeln(sprintf('>>>>> /bin/bash -exc \'%s\'', str_replace('\'', '\\\'', $this->twig->render($name, ['input' => $input]))));
                        $process = Process::fromShellCommandline(sprintf('/bin/bash -exc \'%s\'', str_replace('\'', '\\\\\'', )));
                        $process->setTty(true);
                        $process->run(function() {
                            var_dump(func_get_args());
                        });
                    }
                }

//                $process = new Process(['/bin/bash', '-ex'], null, null);
//                $process->setInput((function() use ($input) {
//                    foreach (['pre', 'exec', 'post'] as $group) {
//                        foreach ($this->cnf[$group] as $index => $line) {
//                            $name = $this->getName() . '.' . $group . '[' . $index . ']';
//                            yield sprintf("  # %s\n  %s\n", $name, $this->twig->render($name, ['input' => $input]));
//                        }
//                    }
//                })());


                $process->start(function($e, $v) {
                    echo $v;
                });

                $output->writeln($process->wait());
                $output->writeln("done....");
//                var_dump($this->cnf);
//
//
//                foreach ($this->cnf['exec'] as $index => $line) {
//                    $output->writeln('<comment>' . $this->getName() . '.exec.' . $index . '</comment>');
//                    $output->writeln($this->twig->render($this->getName() . '.exec.' . $index, ['input' => $input]));
//                }
            }
        };
    }

    public function getAvailableNames() :array
    {
        static $names;
        if (!$names) {
            $names = array_merge(array_keys($this->container->get(AppConfig::class)->getTasks()), array_keys($this->staticCommands));
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