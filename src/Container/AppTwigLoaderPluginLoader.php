<?php
use App\AppConfig;
use App\Twig\Loader\PluginLoader;

return new PluginLoader($this->get(AppConfig::class));