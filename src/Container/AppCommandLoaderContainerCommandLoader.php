<?php
use App\CommandLoader\ContainerCommandLoader;
use App\Command\DebugConfigDumpCommand;
use App\Command\DebugConfigDumpReferenceCommand;
use App\Command\DebugPrintTemplatesCommand;

return new ContainerCommandLoader(
    $this,
    DebugConfigDumpCommand::class,
    DebugConfigDumpReferenceCommand::class,
    DebugPrintTemplatesCommand::class
);