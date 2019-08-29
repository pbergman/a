<?php

use App\Command\DebugConfigDumpCommand;
use App\Command\DebugConfigDumpReferenceCommand;
use App\Command\DebugPrintTemplatesCommand;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

return new FactoryCommandLoader([
    DebugConfigDumpReferenceCommand::getDefaultName() => function() {
        return $this->get(DebugConfigDumpReferenceCommand::class);
    },
    DebugConfigDumpCommand::getDefaultName() => function() {
        return $this->get(DebugConfigDumpCommand::class);
    },
    DebugPrintTemplatesCommand::getDefaultName() => function() {
        return $this->get(DebugPrintTemplatesCommand::class);
    },
]);