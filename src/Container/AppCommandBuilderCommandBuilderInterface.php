<?php
use App\Config\AppConfig;
use App\CommandBuilder\DynamicCommandBuilder;
use App\Exec\ExecInterface;
use App\ShellScript\ShellScriptFactoryInterface;

return new DynamicCommandBuilder($this->get(AppConfig::class), $this->get(ShellScriptFactoryInterface::class),  $this->get(ExecInterface::class));