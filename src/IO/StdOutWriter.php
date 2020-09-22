<?php
declare(strict_types=1);

namespace App\IO;

class StdOutWriter extends StreamWriter
{
    public function __construct()
    {
        parent::__construct('php://stdout');
    }

    public function close(): bool
    {
        return true;
    }
}