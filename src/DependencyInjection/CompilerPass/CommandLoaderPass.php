<?php
declare(strict_types=1);

namespace App\DependencyInjection\CompilerPass;

use App\CommandLoader\CommandLoader;
use App\CommandLoader\ContainerCommandLoader;
use App\CommandLoader\TasksCommandLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CommandLoaderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container
            ->getDefinition(CommandLoader::class)
            ->setArguments([
                new Reference(TasksCommandLoader::class),
                new Reference(ContainerCommandLoader::class),
            ]);

        $args = [];

        foreach (array_keys($container->findTaggedServiceIds('app.command')) as $command) {
            $args[] = $command;
        }

        $container
            ->getDefinition(ContainerCommandLoader::class)
            ->setArgument('$commands', $args);
    }
}