<?php
declare(strict_types=1);

namespace App\DependencyInjection\CompilerPass;

use App\Twig\NodeVisitor\MacroNodeVisitor;
use App\Twig\NodeVisitor\NodeVisitorContainer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class NodeVisitorContainerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container
            ->getDefinition(NodeVisitorContainer::class)
            ->setArgument(0, new Reference(MacroNodeVisitor::class));

    }
}