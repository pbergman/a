<?php
use App\CommandLoader\CommandLoader;
use App\CommandLoader\ContainerCommandLoader;
use App\CommandLoader\TasksCommandLoader;

return new CommandLoader(
    $this->get(TasksCommandLoader::class),
    $this->get(ContainerCommandLoader::class)
);