<?php
namespace App\Command;

use App\AppConfig;
use App\Twig\Loader\PluginLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DebugPrintTemplatesCommand extends Command
{
    /** @var PluginLoader  */
    private $loader;

    protected static $defaultName = 'debug:print:templates';

    public function __construct(PluginLoader $loader)
    {
        parent::__construct();
        $this->loader = $loader;
    }


    protected function configure()
    {
        $this->setDescription('Print all the available templates.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        foreach ($this->loader->getTasks() as $task) {

            $output->writeln('<comment>' . $task . '</comment>');

            $table = new Table($output);
            $table->setStyle('compact');
            $table->setHeaders(['name', 'content']);

            foreach ($this->loader->getKeysFor($task) as $name) {
                $table->addRow([$name, $this->loader->getSourceContext($name)->getCode()]);
            }

            $table->render();

        }

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
