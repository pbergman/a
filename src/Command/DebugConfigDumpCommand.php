<?php
namespace App\Command;

use App\AppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DebugConfigDumpCommand extends Command
{
    /** @var AppConfig  */
    private $config;

    protected static $defaultName = 'debug:config:dump';

    public function __construct(AppConfig $config)
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
        $output->writeln(Yaml::dump($this->config->getConfig(), 10));
    }
}
