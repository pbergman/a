<?php
namespace App\ShellScript;

use App\Plugin\PluginConfig;

interface ShellScriptFactoryInterface
{
    /**
     * @param resource $fd
     * @param string $name
     * @param PluginConfig $cnf
     * @param array $ctx
     */
    public function create($fd, string $name, PluginConfig $cnf, array $ctx = []);
}