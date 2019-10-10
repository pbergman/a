<?php
declare(strict_types=1);

namespace App\DependencyInjection;

use App\Config\AppConfigFile;
use App\Helper\FileHelper;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class TwigCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var InputInterface $input */
        $input = $container->get(InputInterface::class);

        if (false !== $cache = !$input->hasParameterOption(['--no-cache', '-N'], true)) {
            // get cache folder, should be something like ~/.cache/a/twig
            $cache = FileHelper::getCacheDir('twig', sha1((string)$container->get(AppConfigFile::class)->getAppConfigFile()));
            // try to create else disable cache because app should still
            // work so make an noop when check failed
            if (!is_dir($cache) && !mkdir($cache, 0700, true) && !is_dir($cache)) {
                $cache = false;
            }
        }

        var_dump($cache);exit;

    }
}