<?php
use App\AppConfig;
use App\Command\ConfigDumpCommand;

return new ConfigDumpCommand($this->get(AppConfig::class));
