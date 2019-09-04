<?php

use App\Config\AppConfig;
use App\Application;
use App\CommandLoader\CommandLoader;
use App\Config\AppConfigFile;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return new Application(
    $this->get(CommandLoader::class),
    $this->get(AppConfig::class),
    $this->get(InputInterface::class),
    $this->get(OutputInterface::class),
    $this->get(AppConfigFile::class)
);