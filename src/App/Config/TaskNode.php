<?php
declare(strict_types=1);

namespace App\Config;

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
                        return ['exec' => $v];
                    })
                ->end()
                ->children()
                    ->scalarNode('name')->end()
                    ->booleanNode('hidden')->defaultFalse()->end()
                    ->append((new ArgNode())())
                    ->append((new OptNode())())
                    ->arrayNode('exec')
                        ->beforeNormalization()
                            ->ifString()
                                ->castToArray()
                            ->end()
                            ->performNoDeepMerging()
                            ->prototype('scalar')->end()
                            ->defaultValue(array())
                        ->end()
                    ->end()
                ->end()
            ->end();
        return $node;
    }
}