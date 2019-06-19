<?php
declare(strict_types=1);

namespace App\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\Console\Input\InputOption;

/**
 * An node definition of the \Symfony\Component\Console\Input\InputOption
 */
class OptNode
{
    public function __invoke() :NodeDefinition
    {
        $node =  new ArrayNodeDefinition('opts');
        $node
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifString()
                    ->then(function($v) {
                        return ['default' => $v, 'mode' => InputOption::VALUE_REQUIRED];
                    })
                ->end()
                ->beforeNormalization()
                    ->ifEmpty()
                    ->then(function() {
                        return ['mode' => InputOption::VALUE_NONE];
                    })
                ->end()
                ->children()
                    ->scalarNode('name')->end()
                    ->scalarNode('shortcut')->defaultNull()->end()
                    ->scalarNode('mode')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function($v) {
                                $mode = 0;
                                foreach (array_map('trim', explode('|', $v)) as $part) {
                                    $name = strtoupper($part);
                                    if (0 !== strpos($name, 'VALUE_')) {
                                        $name = 'VALUE_' . $name;
                                    }
                                    $const = InputOption::class . '::' . $name;
                                    if (!defined($const)) {
                                        throw new InvalidDefinitionException(
                                            'unexpected value "' . $part . '" expected  "none", "required", "optional" or "is_array" (or joined variation with | as delimiter, for example: required|is_array)'
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