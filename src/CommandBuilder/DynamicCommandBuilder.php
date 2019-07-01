<?php
declare(strict_types=1);

namespace App\CommandBuilder;

use App\AppConfig;
use App\Output\CatOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Twig\Environment;

class DynamicCommandBuilder implements CommandBuilderInterface
{
    /** @var AppConfig */
    private $config;
    /** @var Environment */
    private $twig;

    public function __construct(AppConfig $config, Environment $twig)
    {
        $this->twig = $twig;
        $this->config = $config;
    }

    public function getCommand(string $name) :Command
    {
        return new class($name, $this->config->getTasks()[$name], $this->twig) extends Command {

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

//                var_dump($this->cnf);exit;
//
//                $writer = new CatOutput();
//                $out = $writer->write('/bin/bash -siex', $this->getName(), $this->cnf);
                $script = '#!/bin/bash' . "\nset +ex\n";

                foreach (['pre', 'exec', 'post'] as $group) {
                    foreach ($this->cnf[$group] as $index => $line) {

                        $name = $this->getName() . '.' . $group . '[' . $index . ']';
                        $script .= sprintf("# %s\n%s\n", $name, $line);

//                        $output->writeln(sprintf('>>>>> /bin/bash -exc \'%s\'', str_replace('\'', '\\\'', $this->twig->render($name, ['input' => $input])))

//                        $name = $this->getName() . '.' . $group . '[' . $index . ']';
//                        $output->writeln(sprintf('>>>>> /bin/bash -exc \'%s\'', str_replace('\'', '\\\'', $this->twig->render($name, ['input' => $input]))));
//                        $process = Process::fromShellCommandline(sprintf('/bin/bash -exc \'%s\'', str_replace('\'', '\\\\\'', )));
//                        $process->setTty(true);
//                        $process->run(function() {
//                            var_dump(func_get_args());
//                        });
                    }
                }

                $output->writeln($script);

                $file = tempnam(sys_get_temp_dir(), $this->getName());
                file_put_contents($file, $this->twig->createTemplate($script, 'foo')->render(['input' => $input]));

                $process = new Process(['sh', $file]);
                $process->setTty(true);
                $process->run(function() {
                    var_dump(func_get_args());
                });
                //$output->writeln($this->twig->createTemplate($script, 'foo')->render(['input' => $input]));

//                $output->writeln($this->twig->createTemplate($out[0])->render(['input' => $input]));exit;

//                $process = new Process(['/bin/bash', '-ex'], null, null, $this->twig->createTemplate($out[0])->render(['input' => $input]));
//                $process->setTty(true);
//                $process->setInput((function() use ($input) {
//                    foreach (['pre', 'exec', 'post'] as $group) {
//                        foreach ($this->cnf[$group] as $index => $line) {
//                            $name = $this->getName() . '.' . $group . '[' . $index . ']';
//                            yield sprintf("  # %s\n  %s\n", $name, $this->twig->render($name, ['input' => $input]));
//                        }
//                    }
//                })());


//                $process->start(function($e, $v) {
//                    echo $v;
//                });
//
//                $output->writeln($process->wait());
//                $output->writeln("done....");
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
}