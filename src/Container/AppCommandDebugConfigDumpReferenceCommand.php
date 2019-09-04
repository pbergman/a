<?php
use App\Config\AppConfig;
use App\Command\DebugConfigDumpReferenceCommand;
use App\Config\ConfigTreeBuilder;
use App\Plugin\PluginRegistry;

return new DebugConfigDumpReferenceCommand(
    $this->get(AppConfig::class),
    $this->get(ConfigTreeBuilder::class),
    $this->get(PluginRegistry::class)
);