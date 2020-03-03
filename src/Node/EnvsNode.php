<?php
declare(strict_types=1);

namespace App\Node;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class EnvsNode
{
    public function __invoke(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('envs');
        $node
            ->info(<<<EOF
Environment variables that will be used run this application.

When not provided it will use the environment of parent process.
EOF
        )
            ->useAttributeAsKey('name')
            ->scalarPrototype()->end();

        return $node;
    }
}