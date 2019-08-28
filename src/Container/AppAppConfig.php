<?php
use App\AppConfig;
use App\Config\ConfigResources;
use App\Config\ConfigTreeBuilder;
use App\Plugin\PluginRegistry;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\InputInterface;

return new AppConfig(
    $this->get(InputInterface::class),
    $this->get(PluginRegistry::class),
    $this->get(ConfigResources::class),
    $this->get(Processor::class),
    $this->get(ConfigTreeBuilder::class)
);