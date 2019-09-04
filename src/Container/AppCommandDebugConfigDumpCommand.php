<?php
use App\Config\AppConfig;
use App\Command\DebugConfigDumpCommand;

return new DebugConfigDumpCommand($this->get(AppConfig::class));
