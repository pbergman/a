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
The mode node supports multiple formats that will be normalized to an acceptable \$mode argument value for the 
InputOption (one of InputOption::VALUE_*). 

When string is provided it will try te resolve that to one of the InputOption::VALUE_* constants by converting 
the string to uppercase, prefixing with VALUE_ when it not starts with that and splitting the string on |. 


    tasks:
        example:
            opts:
                foo: 
                    # as string
                    mode: is_array|required
                    
                    # as array
                    # mode: 
                    #    - is_array
                    #    - required
                    
                    # as int 
                    # mode: 10                    
                    
    will be normalized to                 
    
    tasks:
        example:
            opts:
                foo: 
                    mode: 10 # InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY
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
            ->end()
            ->normalizeKeys(false);


        return $node;
    }
}