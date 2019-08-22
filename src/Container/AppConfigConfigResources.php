<?php
use App\Config\ConfigResources;
use App\Plugin\PluginRegistry;

return new ConfigResources($this->get(PluginRegistry::class));