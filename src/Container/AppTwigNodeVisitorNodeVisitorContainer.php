<?php
use App\Twig\NodeVisitor\MacroNodeVisitor;
use App\Twig\NodeVisitor\NodeVisitorContainer;

return new NodeVisitorContainer(
    $this->get(MacroNodeVisitor::class)
);