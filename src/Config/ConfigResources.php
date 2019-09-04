<?php
declare(strict_types=1);

namespace App\Config;

use App\Plugin\PluginRegistry;
use Symfony\Component\Yaml\Yaml;

class ConfigResources
{
    const ROOT_INDEX = '_';

    private $configFile;
    private $registry;

    public function __construct(AppConfigFile $configFile, PluginRegistry $registry)
    {
        $this->registry = $registry;
        $this->configFile = $configFile;
    }

    public function getConfigs() :array
    {
        $root = Yaml::parseFile($this->configFile->getAppConfigFile()->getResource());

        if (isset($root['plugins'])) {
            foreach($root['plugins'] as $plugin) {
                $this->registry->register($plugin);
            }
            unset($root['plugins']);
        }

        $data = [
            self::ROOT_INDEX => $this->processConfig(self::ROOT_INDEX, $root),
        ];

        foreach ($this->registry->getConfigResource() as $name => $resource) {
            $data[$name] = $this->processConfig($name, Yaml::parseFile($resource->getResource()));
        }

        return $data;
    }

    private function serialize(string $exec, string $task, string  $plugin, string  $section = 'exec', $index = 0) :string
    {
        return json_encode(['exec' => $exec, 'task' => $task, 'plugin' => $plugin, 'section' => $section, 'index' => $index]);
    }

    private function processConfig(string $name, array $config) :array
    {
        if (!empty($config['tasks'])) {
            foreach ($config['tasks'] as $taskName => &$taskDef) {
                switch (gettype($taskDef)) {
                    case 'string':
                        $taskDef = $this->serialize($taskDef, $taskName, $name);
                        break;
                    case 'array':
                        foreach (['pre', 'post', 'exec'] as $key) {
                            if (isset($taskDef[$key])) {
                                if (is_array($taskDef[$key])) {
                                    foreach ($taskDef[$key] as $i => &$line) {
                                        $line = $this->serialize($line, $taskName, $name, $key, $i);
                                    }
                                } else {
                                    $taskDef[$key] = $this->serialize($taskDef[$key], $taskName, $name, $key);
                                }
                            }
                        }
                        break;
                }
            }
        }
        return $config;
    }
}
