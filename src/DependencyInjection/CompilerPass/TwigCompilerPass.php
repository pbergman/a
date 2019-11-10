<?php
declare(strict_types=1);

namespace App\DependencyInjection\CompilerPass;

use App\Twig\Extension;
use App\Twig\Loader\ChainedProcessSourceContext;
use App\Twig\Loader\ShortLineProcessSourceContext;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Environment;

class TwigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container
            ->getDefinition(ChainedProcessSourceContext::class)
            ->setArguments([
                new Reference(ShortLineProcessSourceContext::class)
            ]);


        $definition = $container->getDefinition(Environment::class);
        $definition->addMethodCall('addExtension', [new Reference(Extension::class)]);

        foreach (array_keys($container->findTaggedServiceIds('twig.extension')) as $extension) {
            $definition->addMethodCall('addExtension', [new Reference($extension)]);
        }

    }
}