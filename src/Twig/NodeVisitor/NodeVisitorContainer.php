<?php

namespace App\Twig\NodeVisitor;

use Twig\NodeVisitor\NodeVisitorInterface;

class NodeVisitorContainer implements \IteratorAggregate
{
    /** @var NodeVisitorInterface[] */
    private $visitors;

    public function __construct(...$visitors)
    {
        $this->visitors = $visitors;
    }

    /** @inheritDoc */
    public function getIterator()
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor instanceof NodeVisitorInterface) {
                yield $visitor;
            }
        }
    }
}