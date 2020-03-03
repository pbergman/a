<?php
declare(strict_types=1);

namespace App\Node;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class GlobalsNode
{
    public function __invoke(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('globals');
        $node
            ->info('extra global context variables')
            ->defaultValue([])
            ->variablePrototype()->end();
        return $node;
    }
}