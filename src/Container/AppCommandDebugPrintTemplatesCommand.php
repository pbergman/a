<?php
use App\Command\DebugPrintTemplatesCommand;
use App\Twig\Loader\PluginLoader;

return new DebugPrintTemplatesCommand($this->get(PluginLoader::class));