<?php
namespace App\Command;

use App\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DebugConfigDumpReferenceCommand extends Command
{
    /** @var array */
    private $plugins;

    protected static $defaultName = 'debug:config:dump-reference';

    public function __construct(array $plugins)
    {
        parent::__construct();
        $this->plugins = $plugins;
    }


    protected function configure()
    {
        $this
            ->setDescription('Dump the default config and corresponding info.')
            ->setHelp(<<<EOH

This command can be used to generate default config:

    debug:config:dump-reference -p <PLUGIN_NAME>

Or to get some information about an node:
    
    debug:config:dump-reference tasks.name.opts 

EOH
)
            ->addOption('plugin', 'p', InputOption::VALUE_REQUIRED, 'Use this plugin as root for the node tree')
            ->addArgument('path', InputArgument::OPTIONAL, 'The node path to dump')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dumper = new YamlReferenceDumper();
        if (null !== $plugin = $input->getOption('plugin')) {
            if (isset($this->plugins[$plugin]) && in_array($plugin, $this->plugins)) {
                throw new \InvalidArgumentException(sprintf('Plugin %s is not registered. Available plugins \'%s\'', $plugin, implode('\', \'', $plugin)));
            }
            if (isset($this->plugins[$plugin])) {
                $class = $this->plugins[$plugin];
            } else {
                $class = $plugin;
            }
            $builder = new TreeBuilder($plugin);
            $root = $builder->getRootNode();
            $class::appendConfiguration($root);
            $output->writeln($dumper->dumpNode($root->getNode(true)));
        } else {
            $config = new Configuration($this->plugins);
            if (null === $path = $input->getArgument('path')) {
                $output->writeln($dumper->dump($config));
            } else {
                $output->writeln($dumper->dumpAtPath($config, $path));
            }
        }
    }
}
