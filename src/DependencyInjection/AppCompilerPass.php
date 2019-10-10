<?php
declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var InputInterface $input */
        $input = $container->get(InputInterface::class);
        $container->setParameter('debug', 3 === (int) getenv('SHELL_VERBOSITY') || $input->hasParameterOption('-vvv', true));
        $container->setParameter('cache', $input->hasParameterOption(['--no-cache', '-N'], true));
    }
}