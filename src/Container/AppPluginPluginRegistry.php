<?php
use App\Plugin\PluginFileLocator;
use App\Plugin\PluginRegistry;
use Composer\Autoload\ClassLoader;

return new PluginRegistry($this->get(PluginFileLocator::class), $this->get(ClassLoader::class));