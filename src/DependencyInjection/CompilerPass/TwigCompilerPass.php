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

//        var_dump($container->findTaggedServiceIds('twig.extension'));exit;
//        /** @var InputInterface $input */
//        $input = $container->get(InputInterface::class);
//
//        if (false !== $cache = !$input->hasParameterOption(['--no-cache', '-N'], true)) {
//            // get cache folder, should be something like ~/.cache/a/twig
//            $cache = FileHelper::getCacheDir('twig', sha1((string)$container->get(AppConfigFile::class)->getAppConfigFile()));
//            // try to create else disable cache because app should still
//            // work so make an noop when check failed
//            if (!is_dir($cache) && !mkdir($cache, 0700, true) && !is_dir($cache)) {
//                $cache = false;
//            }
//        }
//
//        var_dump($cache);exit;

    }
}