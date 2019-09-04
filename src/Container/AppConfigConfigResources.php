<?php
use App\Config\AppConfigFile;
use App\Config\ConfigResources;
use App\Plugin\PluginRegistry;

return new ConfigResources(
    $this->get(AppConfigFile::class),
    $this->get(PluginRegistry::class)
);