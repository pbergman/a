<?php
use App\Config\AppConfig;
use App\Config\ConfigResources;
use App\Config\ConfigTreeBuilder;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Config\Definition\Processor;

return new AppConfig(
    $this->get(ConfigResources::class),
    $this->get(Processor::class),
    $this->get(ConfigTreeBuilder::class),
    $this->get(CacheInterface::class)
);