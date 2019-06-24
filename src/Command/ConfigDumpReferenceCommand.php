<?php

namespace App\Command;

use App\AppConfig;
use App\Config\ConfigTreeBuilder;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigDumpReferenceCommand extends Command
{
    /** @var AppConfig  */
    protected $config;
    /** @var ConfigTreeBuilder */
    protected $builder;

    public function __construct(AppConfig $config, ConfigTreeBuilder $builder)
    {
        parent::__construct('config:dump:reference');
        $this->config = $config;
        $this->builder = $builder;
    }


    protected function configure()
    {
        $this->addArgument('name', InputArgument::OPTIONAL, 'the config name to dump');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $dumper = new YamlReferenceDumper();


        if (!$name) {
            $output->writeln($dumper->dump($this->builder, $name));
        } else {
            $output->writeln($dumper->dumpAtPath($this->builder, $name));
        }
//        try {

//            echo $dumper->dump($this->builder);exit;
//        }

    }
}