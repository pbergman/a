<?php

use App\Twig\NodeVisitor\DebugNodeVisitor;
use App\Twig\NodeVisitor\MacroNodeVisitor;
use App\Twig\NodeVisitor\NodeVisitorContainer;

return new NodeVisitorContainer(
    $this->get(MacroNodeVisitor::class),
    $this->get(DebugNodeVisitor::class)
);