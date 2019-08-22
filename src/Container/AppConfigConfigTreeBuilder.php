<?php
use App\Config\ConfigTreeBuilder;
use App\Plugin\PluginRegistry;

return new ConfigTreeBuilder($this->get(PluginRegistry::class));