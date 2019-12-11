<?php
declare(strict_types=1);

namespace App\Node;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class MacroNode
{
    public function __invoke(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('macros');
        $node
            ->info(<<<EOF
Macros that can be used reusable logic the templates, see:

    https://twig.symfony.com/doc/2.x/tags/macro.html

All macros are autoloaded and should be called with the 
`_self.` prefix. 

To scope macros to an task you should define them under
an task and when set to the root all plugins have access
to that macro.  
EOF
        )
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function($v) {
                        return ['code' => $v, 'args' => []];
                    })
                ->end()
                ->children()
                    ->scalarNode('code')->end()
                    ->arrayNode('args')
                        ->beforeNormalization()
                            ->castToArray()
                        ->end()
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
            ->normalizeKeys(false);

        return $node;
    }
}