<?php

namespace App\Twig\Node;

use Twig\Compiler;
use Twig\Node\Node;

class DebugNode extends Node
{
    public function compile(Compiler $compiler)
    {
        $compiler
            ->write('echo "\n", \'# \', $this->getTemplateName(), "\n";')
            ->raw("\n");
        ;
    }
}