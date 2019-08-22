<?php

namespace App\Config;

use App\Exception\FindInPathException;
use App\Helper\FileHelper;
use App\Node\MacroNode;
use App\Node\TaskNode;
use App\Plugin\PluginRegistry;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigTreeBuilder implements ConfigurationInterface
{
    private $registry;

    public function __construct(PluginRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('a');

        $root = $builder->getRootNode();
        $root
            ->children()
                ->arrayNode('globals')
                    ->info('extra global context variables')
                    ->defaultValue([])
                    ->variablePrototype()->end()
                ->end()
                ->append((new MacroNode())())
                ->scalarNode('shell')
                    ->info(<<<EOI
All tasks will be merged to an shell script to be executed and the global shell will be used for creating the shebang, see:
  
  https://en.wikipedia.org/wiki/Shebang_(Unix)
  
EOI
        )
                    ->defaultValue(FileHelper::findInPath('bash'))
                    ->beforeNormalization()
                        ->ifTrue(function($v) {
                            return $v[0] !== '/';
                        })
                        ->then(function($v) {
                            try {
                                return FileHelper::findInPath($v);
                            } catch (FindInPathException $e) {
                                return $v;
                            }
                        })
                    ->end()
                ->end()
                ->append((new TaskNode())())
            ->end();


        foreach ($this->registry as $plugin) {
            $plugin->appendConfiguration($root);
        }

        return $builder;
    }
}