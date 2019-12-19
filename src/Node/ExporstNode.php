<?php
declare(strict_types=1);

namespace App\Node;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class ExporstNode
{
    public function __invoke(): NodeDefinition
    {
        $node = new ArrayNodeDefinition('exports');
        $node
            ->info('extra environment variables that will exported in head of script')
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->scalarPrototype()->end();

        return $node;
    }
}