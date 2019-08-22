<?php
declare(strict_types=1);

namespace App\Helper;

use App\AppConfig;

class ScriptWriter
{
    /** @var AppConfig */
    private $config;

    public function __construct(AppConfig $config)
    {
        $this->config = $config;
    }

    public function write(string $name, \SplFileObject $file)
    {
        $file->fwrite(sprintf("#!%s\nset -xe\n", $this->config->getConfig('shell')));
        $tasks = $this->config->getTasks()[$name];
        foreach (['pre', 'exec', 'post'] as $group) {
            foreach ($tasks[$group] as $index => $line) {
                if (false !== $offet = strpos($line, '#}') && $line[0] === '{' && $line[1] === '#') {
                    $meta = json_decode(substr($line, 3, $offet - 3), true);
                    $name = '[' . $meta['plugin'] . '][' . $name . '][' . $group . '][' . $index . ']';
                    $line = substr($line, $offet + 2);
                } else {
                    $name = '[' . $name . '][' . $group . '][' . $index . ']';
                }
                $file->fwrite(sprintf("# %s\n%s\n", $name, $line));
            }
        }
    }
}