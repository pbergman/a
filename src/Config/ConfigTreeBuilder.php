<?php
declare(strict_types=1);

namespace App\Config;

use App\Node\GlobalsNode;
use App\Node\MacroNode;
use App\Node\ShellNode;
use App\Node\TaskNode;
use App\Plugin\PluginRegistry;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigTreeBuilder implements ConfigurationInterface
{
    private $registry;

    public function __construct(PluginRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getConfigTreeBuilder()
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

        foreach ($this->registry as $plugin) {
            $plugin->appendConfiguration($root);
        }

        return $builder;
    }
}
