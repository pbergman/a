<?php
use App\AppConfig;
use App\Command\DebugConfigDumpCommand;

return new DebugConfigDumpCommand($this->get(AppConfig::class));
