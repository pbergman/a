<?php
declare(strict_types=1);

namespace App\CommandBuilder;

use App\Config\AppConfig;
use App\Exec\ExecInterface;
use App\ShellScript\ShellScriptFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DynamicCommandBuilder implements CommandBuilderInterface
{
    /** @var AppConfig */
    private $cnf;
    /** @var ShellScriptFactoryInterface */
    private $ssf;
    /** @var ExecInterface */
    private $exec;

    public function __construct(AppConfig $cnf, ShellScriptFactoryInterface $ssf, ExecInterface $exec)
    {
        $this->ssf = $ssf;
        $this->cnf = $cnf;
        $this->exec = $exec;
    }

    public function getCommand(string $name) :Command
    {
        return new class($name, $this->cnf,  $this->ssf, $this->exec) extends Command {
            private $cnf, $ssf, $exec;
            public function __construct(string $name, AppConfig $cnf, ShellScriptFactoryInterface $ssf, ExecInterface $exec)
            {
                $this->cnf = $cnf;
                $this->ssf = $ssf;
                $this->exec = $exec;
                parent::__construct($name);
            }
            protected function configure()
            {
                $cnf = $this->cnf->getTasks()[$this->getName()];
                if (!empty($cnf['help'])) {
                    $this->setHelp($cnf['help']);
                }
                if (!empty($cnf['description'])) {
                    $this->setDescription($cnf['description']);
                }
                foreach ($cnf['opts'] as $name => $opt) {
                    $this->addOption($name, $opt['shortcut'], $opt['mode'], $opt['description'], $opt['default']);
                }
                foreach ($cnf['args'] as $name => $arg) {
                    $this->addArgument($name, $arg['mode'], $arg['description'], $arg['default']);
                }
                if ($cnf['hidden']) {
                    $this->setHidden(true);
                }
                $this->addOption('dump', 'd', InputOption::VALUE_NONE, 'Dump the script instead of executing');
            }
            protected function execute(InputInterface $input, OutputInterface $output)
            {
                if ($input->getOption('dump')) {
                    $this->ssf->create(STDOUT, $this->getName(), $this->cnf, ['input' => $input]);
                } else {
                    try {
                        $script = fopen('php://temp', 'w+');
                        $this->ssf->create($script, $this->getName(), $this->cnf, ['input' => $input]);
                        return $this->exec->exec($script);
                    } finally {
                        fclose($script);
                    }
                }
            }
        };
    }
}