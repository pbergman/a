<?php
declare(strict_types=1);

namespace App\Plugin;

use App\Exception\TaskNotExistException;
use App\Model\TaskEntry;

class PluginConfig
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getAllConfig() :array
    {
        return $this->config;
    }

    public function hasConfig(string $name) :bool
    {
        return array_key_exists($name, $this->config);
    }

    public function getConfig(string $name, $default = null)
    {
        return array_key_exists($name, $this->config) ? $this->config[$name] : $default;
    }

    public function getGlobals() :array
    {
        return $this->getConfig('globals', []);
    }

    public function getTasks() :array
    {
        return $this->getConfig('tasks', []);
    }

    /**
     * @param string|null $task
     * @return array
     */
    public function getMacros(string $task = null) :array
    {

        if  (null === $task) {
            return $this->getConfig('macros', []);
        }

        $tasks = $this->getTasks();

        if (array_key_exists($task, $tasks)) {
            return $tasks[$task]['macros'];
        }

        return [];
    }

    public function getEnvs(string $task = null) :?array
    {
        if (null === $task) {
            return $this->getConfig('envs', []);
        }

        $tasks = $this->getTasks();

        if (array_key_exists($task, $tasks)) {
            return $tasks[$task]['envs'] + $this->getConfig('envs', []);
        }

        return [];
    }

    public function getExports(string $task = null) :?array
    {
        if (null === $task) {
            return $this->getConfig('exports');
        }

        $tasks = $this->getTasks();

        if (array_key_exists($task, $tasks)) {
            return $tasks[$task]['exports'] + $this->getConfig('exports', []);
        }

        return [];
    }

    public function getTaskCode(string $name) :array
    {
        static $cache;
        if (!$cache || false === array_key_exists($name, $cache)) {
            $parts = explode('::', $name, 2);
            $index = null;
            $tasks = $this->getTasks();
            $tmpl = [];
            if (!isset($tasks[$parts[0]])) {
                throw new TaskNotExistException($name);
            }
            switch (count($parts)) {
                // TASK
                case 1:
                    foreach(['pre', 'exec', 'post'] as $section) {
                        for ($i = 0, $c =\count($tasks[$parts[0]][$section]); $i < $c; $i++) {
                            $tmpl[] = sprintf("@include(%s::%s[%d])\n", $parts[0], $section, $i);
                        }
                    }
                    break;
                case 2:
                    if (false !== $pos = strpos($parts[1], '[')) {
                        $index = (int)substr($parts[1], $pos+1, -1);
                        $parts[1] = substr($parts[1], 0, $pos);
                    }
                    if (!isset($index)) {
                        for ($i = 0, $c =\count($tasks[$parts[0]][$parts[1]]); $i < $c; $i++) {
                            $tmpl[] = sprintf("@include(%s::%s[%d])\n", $parts[0], $parts[1], $i);
                        }
                    } else {
                        $tmpl[] = (string)$tasks[$parts[0]][$parts[1]][$index];
                    }
                    break;
            }
            $cache[$name] = $tmpl;
        }
        return $cache[$name];
    }
}