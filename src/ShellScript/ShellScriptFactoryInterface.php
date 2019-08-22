<?php
namespace App\ShellScript;

use App\AppConfig;

interface ShellScriptFactoryInterface
{
    /**
     * @param resource $fd
     * @param string $name
     * @param AppConfig $cnf
     * @param array $ctx
     */
    public function create($fd, string $name, AppConfig $cnf, array $ctx = []);
}