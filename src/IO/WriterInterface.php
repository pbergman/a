<?php
declare(strict_types=1);

namespace App\IO;

interface WriterInterface
{
    public function close() :bool;

    public function writef(string $fmt, ...$args) :int;

    public function write(string $str) :int;

    public function writeln(string $str) :int;

    public function flush() :bool;

    public function getResource();
}