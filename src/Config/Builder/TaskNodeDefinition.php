<?php
declare(strict_types=1);

namespace App\Config\Builder;

use App\Config\TaskNode;
use Symfony\Component\Config\Definition\Builder\NodeParentInterface;
use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;

class TaskNodeDefinition extends VariableNodeDefinition
{
    public function __construct(?string $name, NodeParentInterface $parent = null)
    {
        parent::__construct($name, $parent);
        $this->allowEmptyValue = false;
    }


    protected function instantiateNode()
    {
        return new TaskNode($this->name, $this->parent, $this->pathSeparator);
    }
}
