<?php

use App\AppConfig;
use App\Command\ConfigDumpReferenceCommand;
use App\Config\ConfigTreeBuilder;
use App\Plugin\PluginRegistry;

return new ConfigDumpReferenceCommand(
    $this->get(AppConfig::class),
    $this->get(ConfigTreeBuilder::class),
    $this->get(PluginRegistry::class)
);