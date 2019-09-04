<?php
use App\Config\AppConfig;
use App\CommandBuilder\CommandBuilderInterface;
use App\CommandLoader\TasksCommandLoader;

return new TasksCommandLoader(
    $this->get(AppConfig::class),
    $this->get(CommandBuilderInterface::class)
);