<?php
declare(strict_types=1);

namespace App\Node;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

class HeaderNode
{

    public function __invoke() :NodeDefinition
    {
        $node = new ScalarNodeDefinition('header');
        $node
            ->info(<<<EOI
This will be places after the shebang line and can be used to set options or environment vars.
EOI
        )
            ->defaultValue("set -e{%- if output.isDebug() -%} x {%- endif -%}\n")
            ->beforeNormalization()
            ->always(function($v) {
                if (is_array($v)) {
                    $v = implode("\n", $v);
                }
                if (empty($v)) {
                    $v = null;
                }
                return $v;
            })
            ->end();
        return $node;
    }
}