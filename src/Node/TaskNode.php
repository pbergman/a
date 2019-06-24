<?php
declare(strict_types=1);

namespace App\Node;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class TaskNode
{
    public function __invoke() :NodeDefinition
    {
        $node = new ArrayNodeDefinition('tasks');
        $node
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifTrue(function($v){
                        return is_string($v);
                    })
                    ->then(function($v) {
                        return ['exec' => [$v]];
                    })
                ->end()
                ->children()
                    ->scalarNode('name')->end()
                    ->scalarNode('help')->defaultNull()->end()
                    ->scalarNode('description')->defaultNull()->end()
                    ->booleanNode('hidden')->defaultFalse()->end()
                    ->append((new ArgNode())())
                    ->append((new OptNode())())
                    ->append((new PrePostNode())('pre'))
                    ->append((new PrePostNode())('post'))
                    ->arrayNode('exec')
                        ->performNoDeepMerging()
                        ->beforeNormalization()
                            ->ifString()
                            ->castToArray()
                        ->end()
                        ->scalarPrototype()->end()
                        ->defaultValue([])
                    ->end()
                ->end()
            ->end();
        return $node;
    }
}