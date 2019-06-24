<?php

namespace App\Command;

use App\AppConfig;
use App\Config\ConfigTreeBuilder;
use App\Node\TaskNode;
use App\Plugin\PluginRegistry;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigDumpReferenceCommand extends Command
{
    /** @var AppConfig  */
    private $config;
    /** @var ConfigTreeBuilder */
    private $builder;
    /** @var PluginRegistry  */
    private $registry;

    protected static $defaultName = 'config:dump:reference';

    public function __construct(AppConfig $config, ConfigTreeBuilder $builder, PluginRegistry $registry)
    {
        parent::__construct();
        $this->config = $config;
        $this->builder = $builder;
        $this->registry = $registry;
    }


    protected function configure()
    {
        $this
            ->setDescription('Dump all the config reference or from the given node path.')
            ->addOption('plugin', 'p', InputOption::VALUE_REQUIRED, 'Use this plugin as root for the node tree')
            ->addArgument('path', InputArgument::OPTIONAL, 'The node path to dump')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dumper = new YamlReferenceDumper();

        if (null !== $plugin = $input->getOption('plugin')) {

            if (!$this->registry->isRegistered($plugin)) {
                $this->registry->register($plugin);
            }

            $builder = new TreeBuilder($plugin);
            $root = $builder->getRootNode();
            $this->registry->getPlugin($plugin)->appendConfiguration($root);
            $output->writeln($dumper->dumpNode($root->getNode(true)));

        } else {
            if (null === $name = $input->getArgument('path')) {
                $output->writeln($dumper->dump($this->builder));
            } else {
                $output->writeln($dumper->dumpAtPath($this->builder, $name));
            }
        }
    }
}