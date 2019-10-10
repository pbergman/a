<?php
declare(strict_types=1);

namespace App\DependencyInjection;

use Composer\Autoload\ClassLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AutoLoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $content = file_get_contents(__DIR__ . '/../../vendor/composer/autoload_real.php');

        if (false == preg_match('/class (?P<class>ComposerAutoloader[^\s]+)/', $content, $m)) {
            throw new \RuntimeException('Failed to get auto generated ComposerAutoloader classname.');
        }

        $container->setDefinition(ClassLoader::class, (new Definition(ClassLoader::class))->setFactory([$m['class'], 'getLoader']));
    }
}