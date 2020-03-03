<?php
declare(strict_types=1);

namespace App\DependencyInjection\CompilerPass;

use App\Twig\Extension;
use App\Twig\Loader\ChainedProcessSourceContext;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Environment;

class TwigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        $refs = [];

        foreach (array_keys($container->findTaggedServiceIds('app.twig.process_source_context')) as $extension) {
            if ($extension !== ChainedProcessSourceContext::class) {
                $refs[] = new Reference($extension);
            }
        }

        $container
            ->getDefinition(ChainedProcessSourceContext::class)
            ->setArguments($refs);

        $definition = $container->getDefinition(Environment::class);

        foreach (array_keys($container->findTaggedServiceIds('twig.extension')) as $extension) {
            $definition->addMethodCall('addExtension', [new Reference($extension)]);
        }


    }
}