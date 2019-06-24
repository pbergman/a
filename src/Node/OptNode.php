<?php
declare(strict_types=1);

namespace App\Node;

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
            ->info(<<<EOF
An option can be as simple as key with null value which will normalized to an 
option that not accepts an value (bool option):

tasks:
    example:
        opts:
            foo: ~
            
will be normalized to:

tasks:
    example:
        opts:
            foo: 
                mode: 1 # InputOption::VALUE_NONE
                
          
An other short hand is just to provide an key value pair which will be normalized to 
an option with and value is required and the value is the default:

tasks:
    example:
        opts:
            foo: bar
            
will be normalized to:

tasks:
    example:
        opts:
            foo: 
                mode: 2 # InputOption::VALUE_REQUIRED
                default: bar

EOF
)
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
                        ->info(<<<EOF
Mode value supports multiple formats that can be normalized to an \$mode argument value for the InputOption. 

    /**
     * @param string                        \$name        The option name
     * @param string|array|null             \$shortcut    The shortcuts, can be null, a string of shortcuts delimited by | or an array of shortcuts
     * @param int|null                      \$mode        The option mode: One of the VALUE_* constants
     * @param string                        \$description A description text
     * @param string|string[]|int|bool|null \$default     The default value (must be null for self::VALUE_NONE)
     *
     * @throws InvalidArgumentException If option mode is invalid or incompatible
     */
    public function __construct(string \$name, \$shortcut = null, int \$mode = null, string \$description = '', \$default = null)
    {


The normalizer will tanslate string values 
"none", "required", "optional" or "is_array" to there InputOption::VALUE_* const and the string 
will be split on on | so that you can alsojoin values like: is_array|required. An alternative to 
this is to giv an array [is_array, required]. The last supported format is to give an int value 
that represents the the cont value. So for example 10 which equals: VALUE_REQUIRED|VALUE_IS_ARRAY.
EOF
                        )
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