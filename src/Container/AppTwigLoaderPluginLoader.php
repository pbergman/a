<?php
use App\Config\AppConfig;
use App\Plugin\PluginRegistry;
use App\Twig\Loader\ProcessSourceContextInterface;
use App\Twig\Loader\PluginLoader;

return new PluginLoader(
    $this->get(AppConfig::class),
    $this->get(PluginRegistry::class),
    $this->get(ProcessSourceContextInterface::class)
);