<?php
declare(strict_types=1);

namespace App\Node;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * An node definition of the \Symfony\Component\Console\Input\InputArgument
 */
class ArgNode
{
    public function __invoke() :NodeDefinition
    {
        $node = new ArrayNodeDefinition('args');
        $node
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function($v) {
                        return ['default' => $v, 'mode' => InputArgument::REQUIRED];
                    })
                ->end()
                ->beforeNormalization()
                    ->ifEmpty()
                    ->then(function() {
                        return ['mode' => InputArgument::REQUIRED];
                    })
                ->end()
                ->children()
                    ->scalarNode('name')->end()
                    ->scalarNode('mode')
                         ->beforeNormalization()
                            ->ifArray()
                            ->then(function($v) {
                                return implode('|', $v);
                            })
                        ->end()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function($v) {
                                $mode = 0;
                                foreach (array_map('trim', explode('|', $v)) as $part) {
                                    $const = InputArgument::class . '::' . strtoupper($part);
                                    if (!defined($const)) {
                                        throw new InvalidDefinitionException(
                                            'unexpected value "' . $part . '" expected "required", "optional" or "is_array" (or joined variation with an | delimiter, for example: required|is_array)'
                                        );
                                    }
                                    $mode |= constant($const);
                                }
                                return $mode;
                            })
                        ->end()
                        ->defaultNull()
                    ->end()
                    ->scalarNode('description')->defaultValue('')->end()
                    ->scalarNode('default')->defaultNull()->end()
                ->end()
            ->end();
        return $node;
    }
}
