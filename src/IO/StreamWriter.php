<?php
declare(strict_types=1);

namespace App\IO;

use App\Exception\WriterException;

class StreamWriter implements WriterInterface
{
    private $fp;
    private $closed;

    public function __construct(string $filename, string $mode = 'wb+')
    {
        if (false === $fp = @fopen($filename, $mode)) {
            throw new WriterException('Failed to open file: "' . $filename . '"');
        }

        $this->fp = $fp;
        $this->closed = false;
    }

    public function close() :bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        return fclose($this->fp);
    }

    public function writef(string $fmt, ...$args) :int
    {
        return (int)fprintf($this->fp, $fmt, ...$args);
    }

    public function write(string $str) :int
    {
        return (int)fwrite($this->fp, $str);
    }

    public function writeln(string $str) :int
    {
        return (int)fwrite($this->fp, $str . "\n");
    }

    public function flush() :bool
    {
        return fflush($this->fp);
    }

    public function getResource()
    {
        return $this->fp;
    }
}