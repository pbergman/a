<?php
use App\CommandLoader\CommandLoader;
use App\CommandLoader\TasksCommandLoader;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

return new CommandLoader($this->get(TasksCommandLoader::class), $this->get(FactoryCommandLoader::class));