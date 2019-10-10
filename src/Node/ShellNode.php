<?php
declare(strict_types=1);

namespace App\Node;

use App\Exception\FindInPathException;
use App\Helper\FileHelper;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

class ShellNode
{

    public function __invoke() :NodeDefinition
    {
        $node = new ScalarNodeDefinition('shell');
        $node
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
            ->end();
        return $node;
        return $node;
    }
}