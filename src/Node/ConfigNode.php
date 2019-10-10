<?php
declare(strict_types=1);

namespace App\Node;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ConfigNode
{
    public function __invoke(ArrayNodeDefinition $node)
    {
        $node
            ->addDefaultChildrenIfNoneSet()
            ->children()
                ->arrayNode('twig')
                    ->children()
                        ->booleanNode('debug')->end()
                        ->scalarNode('charset')->end()
                        ->scalarNode('base_template_class')->end()
                        ->booleanNode('strict_variables')->defaultTrue()->end()
                        ->scalarNode('autoescape')->defaultFalse()->end()
                        ->booleanNode('cache')->end()
                        ->booleanNode('auto_reload')->defaultTrue()->end()
                        ->integerNode('optimizations')->end()
                    ->end()
                ->end()
                ->booleanNode('debug')->defaultFalse()->end()
                ->booleanNode('cach')->defaultTrue()->end()
            ->end();
    }
}