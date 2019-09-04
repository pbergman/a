<?php
use App\Config\AppConfig;
use App\Command\DebugPrintTemplatesCommand;
use App\Twig\Loader\PluginLoader;

return new DebugPrintTemplatesCommand(
    $this->get(AppConfig::class),
    $this->get(PluginLoader::class)
);