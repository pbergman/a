<?php
namespace App\Exec;

interface ExecInterface
{
    public function exec($script, $stdout = null, $stderr = null) :int;
}