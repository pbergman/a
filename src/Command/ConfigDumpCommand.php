<?php

namespace App\Command;

use App\AppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigDumpCommand extends Command
{
    /** @var AppConfig  */
    private $config;

    protected static $defaultName = 'config:dump';

    public function __construct(AppConfig $config)
    {
        parent::__construct();
        $this->config = $config;
    }


    protected function configure()
    {
        $this
            ->setDescription('Dump the config.')
            ->addArgument('path', InputArgument::OPTIONAL, 'The node path to dump')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln(Yaml::dump($this->config->getConfig(), 10));

//        $dumper = new YamlReferenceDumper();
//
//        if (null !== $plugin = $input->getOption('plugin')) {
//
//            if (!$this->registry->isRegistered($plugin)) {
//                $this->registry->register($plugin);
//            }
//
//            $builder = new TreeBuilder($plugin);
//            $root = $builder->getRootNode();
//            $this->registry->getPlugin($plugin)->appendConfiguration($root);
//            $output->writeln($dumper->dumpNode($root->getNode(true)));
//
//        } else {
//            if (null === $name = $input->getArgument('path')) {
//                $output->writeln($dumper->dump($this->builder));
//            } else {
//                $output->writeln($dumper->dumpAtPath($this->builder, $name));
//            }
//        }
    }
}
