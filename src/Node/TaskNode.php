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
                    ->append((new MacroNode())())
                    ->append((new ArgNode())())
                    ->append((new OptNode())())
                    ->append((new PrePostNode())('pre'))
                    ->append((new PrePostNode())('post'))
                    ->arrayNode('exec')
                        ->info(<<<EOF
Similar to pre and post with the exception this won`t be merged and will be 
overwritten and only accepts strings or array of strings as value.
EOF
                        )
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