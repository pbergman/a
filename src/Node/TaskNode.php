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
            ->beforeNormalization()
                ->always(function($values) {
                    $ret = [];

                    foreach ($values as $key => $value) {
                        if (false !== strpos($key, '.') && false === strpos($key, ':')) {
                            $key = str_replace('.', ':', $key);
                        }

                        $ret[$key] = $value;
                    }

                    return $ret;
                })
            ->end()
            ->normalizeKeys(false)
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
                    ->booleanNode('abstract')->defaultFalse()->end()
                    ->scalarNode('name')->end()
                    ->scalarNode('help')->defaultNull()->end()
                    ->scalarNode('description')->defaultNull()->end()
                    ->booleanNode('hidden')->defaultFalse()->end()
                    ->append((new ExporstNode())())
                    ->append((new EnvsNode())())
                    ->append((new MacroNode())())
                    ->append((new ArgNode())())
                    ->append((new OptNode())())
                    ->append((new PrePostNode())('pre'))
                    ->append((new PrePostNode())('post'))
                    ->arrayNode('exec')
                        ->info(<<<EOF
Similar to pre and post with the exception this won`t be merged
and only accepts a strings or array of strings as value.
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