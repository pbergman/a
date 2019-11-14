<?php

namespace App\Exec;

use App\Exec\Exception\ExecFailedException;
use App\Exec\Exception\InvalidArgumentException;
use App\Exec\Exception\RuntimeException;

class BashScriptExec implements ExecInterface
{
    /** @var int */
    private $fd;
    /** @var array */
    private $base;

    public function __construct(int $fd = 3, array $base = ['/dev/fd', '/proc/self/fd'])
    {
        $this->fd = $fd;
        $this->base = $base;
    }

    /**
     * @param resource $script
     * @param resource|null $stdout
     * @param resource|null $stderr
     * @return int
     */
    public function exec($script, $stdout = null, $stderr = null) :int
    {
        if (!is_resource($script)) {
            throw new InvalidArgumentException(sprintf('Script argument must be a valid resource type. %s given.' . gettype($script)));
        }
        $proc = proc_open(sprintf('bash -c \'source %s\'', $this->filepath()), $this->getDescriptors($script, $stdout, $stderr), $pipes);
        $stat = ['signaled' => false, 'stopped' => false, 'exitcode' => -1];
        if (is_resource($proc)) {
            while (true) {
                if (false !== $stat = proc_get_status($proc)){
                    if ($stat['running']) {
                        if (isset($pipes[1])) {
                            $this->copy($pipes[1], $stdout);
                        }
                        if (isset($pipes[2])) {
                            $this->copy($pipes[2], $stderr);
                        }
                    } else {
                        if (isset($pipes[1])) {
                            fclose($pipes[1]);
                        }
                        if (isset($pipes[2])) {
                            fclose($pipes[2]);
                        }
                        break;
                    }
                } else {
                    throw new RuntimeException('Could not get proc status');
                }
            }
        } else {
            throw new RuntimeException('Cannot execute child process');
        }
        if ($stat['signaled']) {
            throw new ExecFailedException(sprintf('The child process has been terminated by an uncaught signal (%d).', $stat['termsig']));
        }
        if ($stat['stopped']) {
            throw new ExecFailedException(sprintf('The child process has been stopped by a signal (%d).', $stat['stopsig']));
        }
        return $stat['exitcode'];
    }

    private function getDescriptors($script, $stdout = null, $stderr = null) :array
    {
        if (null === $stdout) {
            $stdout = STDOUT;
        }
        if (null === $stderr) {
            $stderr = STDERR;
        }
        return [
            0 => STDIN,
            1 => (STDOUT === $stdout) ? $stdout : ['pipe', 'w'],
            2 => (STDERR === $stderr) ? $stderr : ['pipe', 'w'],
            $this->fd => $script,
        ];
    }

    private function copy($a, $b) :void {
        while (!feof($a)) {
            if (false !== $buf = fread($a, 4096)) {
                if (false === fwrite($b, $buf)) {
                    throw new RuntimeException('Failed to write content to stream');
                }
            } else {
                throw new RuntimeException('Failed to read content from stream');
            }
        }
    }

    private function getBaseDir() :string
    {
        static $dir;
        if (!$dir) {
            foreach ($this->base as $d) {
                if (is_dir($d)) {
                    $dir = $d;
                }
            }
            if (!$dir) {
                throw new RuntimeException('Could not find the file description dir, looked in "%s"', implode('", "', $this->base));
            }
        }
        return $dir;
    }

    private function filepath() :string
    {
        $base = $this->getBaseDir();
        if (DIRECTORY_SEPARATOR !== substr($base, -1)) {
            $base .= DIRECTORY_SEPARATOR;
        }
        return $base . $this->fd;
    }
}
