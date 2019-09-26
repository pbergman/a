<?php
use App\Plugin\PluginFileLocator;
use App\Plugin\PluginRegistry;
use Composer\Autoload\ClassLoader;
use Twig\Environment;

return new PluginRegistry(
    $this->get(PluginFileLocator::class),
    $this->get(ClassLoader::class),
    $this->get(Environment::class)
);