<?php

namespace App\Twig\NodeVisitor;

use App\Twig\Node\DebugNode;
use Twig\Environment;
use Twig\Node\BlockNode;
use Twig\Node\BodyNode;
use Twig\Node\IncludeNode;
use Twig\Node\MacroNode;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;
use Twig\Profiler\Node\EnterProfileNode;
use Twig\Profiler\Node\LeaveProfileNode;
use Twig\Profiler\Profile;

class DebugNodeVisitor extends AbstractNodeVisitor
{
    private $extensionName;

    public function __construct(string $extensionName = null)
    {
        $this->extensionName = $extensionName;
    }

    protected function doEnterNode(Node $node, Environment $env)
    {
        return $node;
    }

    protected function doLeaveNode(Node $node, Environment $env)
    {

        if ($node instanceof ModuleNode) {
//            var_dump($node);
            $varName = $this->getVarName();
//            var_dump($node);exit;
//var_dump($node->getNode('display_end'));exit;
            $node->setNode('display_start', new Node([new DebugNode(), $node->getNode('display_start')]));
//            $node->setNode('display_end', new Node([new LeaveProfileNode($varName), $node->getNode('display_end')]));
        }
//        else if ($node instanceof IncludeNode) {
//            var_dump($node);
//        }

//        if ($node instanceof ModuleNode) {
//            $varName = $this->getVarName();
//            $node->setNode('display_start', new Node([new EnterProfileNode($this->extensionName, Profile::TEMPLATE, $node->getTemplateName(), $varName), $node->getNode('display_start')]));
//            $node->setNode('display_end', new Node([new LeaveProfileNode($varName), $node->getNode('display_end')]));
//        } elseif ($node instanceof BlockNode) {
//            $varName = $this->getVarName();
//            $node->setNode('body', new BodyNode([
//                new EnterProfileNode($this->extensionName, Profile::BLOCK, $node->getAttribute('name'), $varName),
//                $node->getNode('body'),
//                new LeaveProfileNode($varName),
//            ]));
//        } elseif ($node instanceof MacroNode) {
//            $varName = $this->getVarName();
//            $node->setNode('body', new BodyNode([
//                new EnterProfileNode($this->extensionName, Profile::MACRO, $node->getAttribute('name'), $varName),
//                $node->getNode('body'),
//                new LeaveProfileNode($varName),
//            ]));
//        }

        return $node;
    }

    private function getVarName(): string
    {
        return sprintf('__internal_%s', hash('sha256', $this->extensionName));
    }

    public function getPriority()
    {
        return 0;
    }
}
