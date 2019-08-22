<?php


//$descriptorspec = array(
//    0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
//    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
//    2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
//);

//$tmp = fopen('/tmp/error-output.txt', 'wb+');
//fwrite($tmp, "#!/bin/bash\nssh -tq testing 'cd ; bash --login'");
//fclose($tmp);

use Symfony\Component\Console\Output\OutputInterface;
//
include_once 'vendor/autoload.php';
//
//class StreamLogger
//{
//
//    public $context;
//
//    /** @var string */
//    private $buf;
//    /** @var OutputInterface */
//    private $output;
//    /** @var string */
//    private $prefix = '';
//
//    public function stream_write(string $data): int
//    {
//        $lines = explode("\n", $this->buf . $data);
//
//        if ($lines[count($lines) - 1] === "") {
//            $this->buf = '';
//        } else {
//            $this->buf = $lines[count($lines) - 1];
//        }
//var_dump($data);
//        for ($i = 0, $c = count($lines) - 1; $i < $c; $i++) {
//            $this->output->writeln($this->prefix . $lines[$i]);
//        }
//    }
//
//    public function stream_close(): void
//    {
//
//    }
//
//    public function stream_cast()
//    {
//        if ($this->output instanceof \Symfony\Component\Console\Output\StreamOutput) {
//            return $this->output->getStream();
//        }
//
//        return false;
//    }
//
//    public function stream_open($path, $mode, $options, &$opened_path) :bool
//    {
//        $opts = stream_context_get_options($this->context);
//
//        if (!isset($opts['io'])) {
//            return false;
//        }
//
//        $opts = $opts['io'];
//
//        if (false === array_key_exists('output', $opts)) {
//            return false;
//        }
//
//        $this->output = $opts['output'];
//
//        if (!empty($opts['prefix'])) {
//            $this->prefix = $opts['prefix'];
//        }
//
//        return true;
//    }
//}

//stream_wrapper_register('io', StreamLogger::class);
$logger = new Symfony\Component\Console\Output\StreamOutput(STDOUT);
//$stdout = fopen('io://stdout', 'w+', false, stream_context_create(['io' => ['output' => $logger, 'prefix' => '>> ']]));


$script = fopen("php://temp", "w+");

fwrite($script, "#!/bin/bash\n");
fwrite($script, "set -ex\n");
fwrite($script, "echo 'hello world'\n");
fwrite($script, "ssh git\n");
//fwrite($script, "read -p \"Are you sure? \" -n 10 -r\n");
fwrite($script, "exit 2\n");
rewind($script);


$stdout = fopen('php://temp', 'w+');
$stderr = fopen('php://temp', 'w+');



class BashScriptExec
{
    /** @var int */
    private $fd;
    /** @var array */
    private $base;
    /** @var array */
    private $descriptors;

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
            throw new \InvalidArgumentException(sprintf('Script argument must be a valid resource type. %s given.', $script));
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
                    throw new \RuntimeException('Could not get proc status');
                }
            }
        } else {
            throw new \RuntimeException('Cannot execute child process');
        }

        if ($stat['signaled']) {
            throw new \RuntimeException(sprintf('The child process has been terminated by an uncaught signal (%d).', $stat['termsig']));
        }

        if ($stat['stopped']) {
            throw new \RuntimeException(sprintf('The child process has been stopped by a signal (%d).', $stat['stopsig']));
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

    private function copy($a, $b) {
        while (!feof($a)) {
            if (false !== $buf = fread($a, 4096)) {
                if (false === fwrite($b, $buf)) {
                    throw new \RuntimeException('failed to write content to stream');
                }
            } else {
                throw new \RuntimeException('failed to read content from stream');
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
                throw new \RuntimeException('could not find the file description dir, looked in "%s"', implode('", "', $this->base));
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

$exec = new BashScriptExec();
echo $exec->exec($script);