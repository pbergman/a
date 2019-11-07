<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DebugEnvCommand extends Command
{
    protected static $defaultName = 'debug:config:dump-env';


    protected function configure()
    {
        $this
            ->setDescription('Dump the runtime config.')
            ->setHelp(<<<EOH

This command will print the runtime config, which are 
all currently available shell variables that start with A_ 

EOH
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $set =  $this->getApplication()->getSetEnvKeys();
        $vars = array_filter(
            getenv(),
            function($n) {
                return 0 === strpos($n, 'A_');
            },
            ARRAY_FILTER_USE_KEY
        );

        $table = new Table($output);
        $table->setHeaders(['name', 'value', 'is default']);
        foreach ($vars as $key => $value) {
            $table->addRow([$key, (string)$value, in_array($key, $set) ? '<info>✓</info>' : '<comment>✗</comment>']);
        }
        $table->render();
    }
}