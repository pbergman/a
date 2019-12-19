<?php
namespace App\Exec;

interface ExecInterface
{
    public function exec($script, array $envs = [], $stdout = null, $stderr = null) :int;
}