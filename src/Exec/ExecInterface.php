<?php
namespace App\Exec;

use App\IO\WriterInterface;

interface ExecInterface
{
    public function exec(WriterInterface $script, array $envs = [], $stdout = null, $stderr = null) :int;
}