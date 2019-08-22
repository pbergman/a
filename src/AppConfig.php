<?php
declare(strict_types=1);

namespace App;

use App\Config\ConfigResources;
use App\Config\ConfigTreeBuilder;
use App\Plugin\PluginRegistry;
use App\Twig\Loader\PluginLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

class AppConfig
{
    /** @var InputInterface */
    private $input;
    /** @var ConfigResources */
    private $resources;
    /** @var Processor */
    private $processor;
    /** @var ConfigTreeBuilder */
    private $builder;
    /** @var PluginRegistry */
    private $registry;
    /** @var array */
    private $config;
    /** @var PluginLoader */
    private $loader;
    /** @var array  */
    private $macros = [];

    public function __construct(InputInterface $input, PluginRegistry $registry, ConfigResources $resources, Processor $processor, ConfigTreeBuilder $builder, PluginLoader $loader)
    {
        $this->input = $input;
        $this->resources = $resources;
        $this->processor = $processor;
        $this->builder = $builder;
        $this->registry = $registry;
        $this->loader = $loader;
    }

    public static function getDefaultConfigFile()
    {
        return getcwd() . '/a.yaml';
    }

    private function getAppConfigFile() :string
    {
        return $this->input->getParameterOption(['--config', '-c'], $this->getDefaultConfigFile(), true);
    }

    private function initConfig()
    {
        $config = Yaml::parseFile($this->getAppConfigFile());
        if (isset($config['plugins'])) {
            foreach ($config['plugins'] as $name) {
                $this->registerPlugin($name);
            }
            unset($config['plugins']);
        }
        $this->config = $this->processor->processConfiguration($this->builder, $this->mergeConfigs($config));

        if (isset($this->config['tasks'])) {
            foreach ($this->config['tasks'] as $name => $task) {
                if (false !== strpos($name, '.')) {
                    $this->config['tasks'][str_replace('.', ':', $name)] = $task;
                    unset($this->config['tasks'][$name]);
                }
            }
        }

        ksort($this->config);
    }

    public function getConfig(string $name = null)
    {
        if (null === $this->config) {
            $this->initConfig();
        }
        return (is_null($name)) ? $this->config : (isset($this->config[$name]) ? $this->config[$name] : null);
    }

    private function mergeConfigs(array $appConfig)
    {
        $config = $this->resources->getConfigs();
        $config['a'] = $appConfig;
        $this->registerTemplates($config);
        return $config;
    }

    public function getTemplateNames(string $task) :array
    {
        return $this->loader->getKeysFor(str_replace(':', '.', $task));
    }

    private function registerTemplates(array $root)
    {
        $macros = isset($root['macros']) ? $root['macros'] : [];
        foreach ($root as $name => $config) {
            if (!empty($config['tasks'])) {
                foreach ($config['tasks'] as $taskName => $taskDef) {
                    switch (gettype($taskDef)) {
                        case 'string':
                            $this->loader->addPlugin($taskDef, $name, $taskName);
                            break;
                        case 'array':
                            foreach (['pre', 'post', 'exec'] as $key) {
                                if (isset($taskDef[$key])) {
                                    if (is_array($taskDef[$key])) {
                                        foreach ($taskDef[$key] as $i => $line) {
                                            $this->loader->addPlugin($line, $name, $taskName, $key);
                                        }
                                    } else {
                                        $this->loader->addPlugin($taskDef[$key], $name, $taskName, $key);
                                    }
                                }
                            }
                            if (isset($taskDef['macros'])) {
                                $macros = array_merge($macros, $taskDef['macros']);
                            }
                            break;
                    }
                }
            }
        }
        $this->macros = $macros;
    }

    public function registerPlugin(string $name) :void
    {
        $this->registry->register($name);
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

    /** @return array */
    public function getMacros(): array
    {
        return $this->macros;
    }
}
