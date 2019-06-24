<?php

namespace App\Config;

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
                ->arrayNode('globals')
                    ->info('extra global context variables')
                    ->defaultValue([])
                    ->variablePrototype()->end()
                ->end()
                ->append((new TaskNode())())
            ->end();


        foreach ($this->registry as $plugin) {
            $plugin->appendConfiguration($root);
        }

        return $builder;
    }
}