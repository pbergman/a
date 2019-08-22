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

These will be printed to the header of the template so you
can call them directly and there is no need to include and
the `_self.` prefix should sufficient.
EOF
        )
                ->defaultValue([])
                ->variablePrototype()->end()
            ->end();
        return $node;
    }
}