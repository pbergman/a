<?php
namespace App\ShellScript;

use App\IO\WriterInterface;
use App\Plugin\PluginConfig;

interface ShellScriptFactoryInterface
{
    /**
     * @param WriterInterface $writer
     * @param string $name
     * @param PluginConfig $cnf
     * @param array $ctx
     */
    public function create(WriterInterface $writer, string $name, PluginConfig $cnf, array $ctx = []);
}