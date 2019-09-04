<?php

use App\Config\AppConfigFile;
use App\Plugin\PluginRegistry;
use App\Twig\Extension;
use App\Helper\FileHelper;
use Symfony\Component\Console\Input\InputInterface;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\LoaderInterface;

if (false !== $this->get(InputInterface::class)->getParameterOption(['--no-cache', '-N'], false, true)) {
    $cache = FileHelper::getCacheDir('twig', sha1((string)$this->get(AppConfigFile::class)->getAppConfigFile()));
    if (!is_dir($cache)) {
        if (false === mkdir($cache, 0700, true)) {
            $cache = false;
        }
    }
} else {
    $cache = false;
}

$instance = new Environment(
    $this->get(LoaderInterface::class),
    [
        'strict_variables' => 1,
        'autoescape' => false,
        'debug' => true,
        'cache' => $cache
    ]
);

foreach ($this->get(PluginRegistry::class) as $plugin) {
    if ($plugin instanceof ExtensionInterface) {
        $instance->addExtension($plugin);
    }
}

$instance->addExtension($this->get(Extension::class));

return $instance;
