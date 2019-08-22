<?php

use App\Command\ConfigDumpCommand;
use App\Command\ConfigDumpReferenceCommand;
use App\Command\DebugPrintTemplatesCommand;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

return new FactoryCommandLoader([
    ConfigDumpReferenceCommand::getDefaultName() => function() {
        return $this->get(ConfigDumpReferenceCommand::class);
    },
    ConfigDumpCommand::getDefaultName() => function() {
        return $this->get(ConfigDumpCommand::class);
    },
    DebugPrintTemplatesCommand::getDefaultName() => function() {
        return $this->get(DebugPrintTemplatesCommand::class);
    },
]);