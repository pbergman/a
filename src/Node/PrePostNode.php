<?php
declare(strict_types=1);

namespace App\Node;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * the pre and post node from an task, this is basically an array node with
 * scalar elements but can als be an array with the properties exec and weight
 * so you can change te position in the merged list.
 */
class PrePostNode
{
    public function __invoke(string $name): NodeDefinition
    {
        $node = new ArrayNodeDefinition($name);
        $node
            ->beforeNormalization()
                ->ifString()
                ->then(function($v) {
                    return [
                        [
                            'weight' => 0,
                            'exec' => $v,
                        ]
                    ];
                })
            ->end()
            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function($v) {
                        return [
                            'weight' => 0,
                            'exec' => $v
                        ];
                    })
                ->end()
                ->children()
                    ->scalarNode('exec')->end()
                    ->integerNode('weight')->defaultValue(0)->end()
                ->end()
            ->end()
            ->validate()
                ->always(function($v) {
                    usort($v, function($a, $b) {
                        return $a['weight'] <=>  $b['weight'];
                    });
                    return array_column($v, 'exec');
                })
            ->end()
            ->defaultValue(array())
        ->end();

        return $node;
    }
}