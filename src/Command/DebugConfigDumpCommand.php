<?php
declare(strict_types=1);

namespace App\Command;

use App\Plugin\PluginConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DebugConfigDumpCommand extends Command
{
    /** @var PluginConfig  */
    private $config;

    protected static $defaultName = 'debug:config:dump';

    public function __construct(PluginConfig $config)
    {
        parent::__construct();
        $this->config = $config;
    }

    protected function configure()
    {
        $this->setDescription('Dump the merged config.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->config->getAllConfig();

        foreach ($config['tasks'] as &$task) {
            foreach (['pre','post','exec'] as $leave) {
                foreach ($task[$leave] as $index => $value) {
                    $task[$leave][$index] = (string)$value;
                }
            }
        }

        $output->writeln(Yaml::dump($config, 10));
    }
}
