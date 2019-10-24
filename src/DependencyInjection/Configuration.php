<?php
declare(strict_types=1);

namespace App\DependencyInjection;

use App\Node\GlobalsNode;
use App\Node\MacroNode;
use App\Node\ShellNode;
use App\Node\TaskNode;
use App\Plugin\PluginInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /** @var array|PluginInterface[]  */
    private $plugins;

    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }


    public function getConfigTreeBuilder() :TreeBuilder
    {
        $builder = new TreeBuilder('a');
        $root = $builder->getRootNode();

        $root
            ->children()
                ->append((new GlobalsNode())())
                ->append((new MacroNode())())
                ->append((new ShellNode())())
                ->append((new TaskNode())())
            ->end();

        foreach ($this->plugins as $plugin) {
            $plugin::appendConfiguration($root);
        }

        return $builder;
    }

}
