<?php
declare(strict_types=1);

namespace App\Config;

use App\Exception\TaskNotExistException;
use App\Model\TaskEntry;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Config\Definition\Processor;

class AppConfig
{
    /** @var ConfigResources */
    private $resources;
    /** @var Processor */
    private $processor;
    /** @var ConfigTreeBuilder */
    private $builder;
    /** @var array */
    private $config;
    /** @var CacheInterface  */
    private $cache;

    public function __construct(ConfigResources $resources, Processor $processor, ConfigTreeBuilder $builder, CacheInterface $cache)
    {
        $this->resources = $resources;
        $this->processor = $processor;
        $this->builder = $builder;
        $this->cache = $cache;
    }

    private function getRawConfig() :array
    {
        return $this->resources->getConfigs();
    }

    private function initConfig()
    {
        if (null === $config = $this->cache->get('config')) {
            $config = $this->normalizeConfig($this->processor->processConfiguration($this->builder, $this->getRawConfig()));
            $this->cache->set('config', $config);
        }

        $this->config = $config;
    }

    private function normalizeConfig(array $cnf) :array
    {
        foreach ($cnf['tasks'] as $name => $task) {

            $real = $this->realName($name);

            if ($real !== $name) {
                $cnf['tasks'][$real] = $task;
                unset($cnf['tasks'][$name]);
            }

            foreach (['pre', 'post', 'exec'] as $section) {
                foreach ($cnf['tasks'][$real][$section] as $index => $line) {
                    $cnf['tasks'][$real][$section][$index] = $this->newTaskEntry($line);
                }
            }
        }

        ksort($cnf['tasks']);

        return $cnf;
    }

    private function newTaskEntry(string $line) :TaskEntry
    {
        $data = json_decode($line, true);

        if ("\n" !== substr($data['exec'], -1)) {
            $data['exec'] .= "\n";
        }

        return new TaskEntry(
            $data['exec'],
            $data['task'],
            $data['plugin'],
            $data['section'],
            $data['index']
        );
    }

    public function getConfig(string $name = null)
    {
        if (null === $this->config) {
            $this->initConfig();
        }
        return (is_null($name)) ? $this->config : (isset($this->config[$name]) ? $this->config[$name] : null);
    }

    public function getGlobals() :array
    {
        if (null === $tasks = $this->getConfig('globals')) {
            return [];
        } else {
            return $tasks;
        }
    }

    public function getTasks() :array
    {
        if (null === $tasks = $this->getConfig('tasks')) {
            return [];
        } else {
            return $tasks;
        }
    }

    /**
     * @param string|null $task
     * @return array
     */
    public function getMacros(string $task = null) :array
    {
        $config = $this->getConfig();
        return (null === $task) ? $config['macros'] : (isset($config['tasks'][$task]) ? $config['tasks'][$task]['macros'] : []);
    }

    private function realName(string $name) :string
    {
        return str_replace('.', ':', $name);
    }

    public function getCode(string $name) :array
    {
        static $cache;
        $name = $this->realName($name);
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
                            $tmpl[] = sprintf("include(%s::%s[%d])\n", $parts[0], $section, $i);
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
