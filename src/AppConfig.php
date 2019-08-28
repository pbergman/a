<?php
declare(strict_types=1);

namespace App;

use App\Config\ConfigResources;
use App\Config\ConfigTreeBuilder;
use App\Plugin\PluginRegistry;
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

    public function __construct(InputInterface $input, PluginRegistry $registry, ConfigResources $resources, Processor $processor, ConfigTreeBuilder $builder)
    {
        $this->input = $input;
        $this->resources = $resources;
        $this->processor = $processor;
        $this->builder = $builder;
        $this->registry = $registry;
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

        $this->config = $this->processor->processConfiguration(
            $this->builder,
            $this->addTemplateMeta(
                $this->getConfigResources(
                    $config
                )
            )
        );

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

    private function getConfigResources(array $rootConfig)
    {
        $config = $this->resources->getConfigs();
        $config['_'] = $rootConfig;
        return $config;
    }

    private function addTemplateMeta(array $cnf) :array
    {
        $meta = function($task, $plugin, $section = 'exec', $index = 0) :string {
            return '{# ' .\json_encode(['task' => $task, 'plugin' => $plugin, 'section' => $section, 'index' => $index]) . ' #}';
        };

        foreach ($cnf as $name => &$config) {
            if (!empty($config['tasks'])) {
                foreach ($config['tasks'] as $taskName => &$taskDef) {
                    switch (gettype($taskDef)) {
                        case 'string':
                            $taskDef = $meta($taskName, $name) . $taskDef;
                            break;
                        case 'array':
                            foreach (['pre', 'post', 'exec'] as $key) {
                                if (isset($taskDef[$key])) {
                                    if (is_array($taskDef[$key])) {
                                        foreach ($taskDef[$key] as $i => &$line) {
                                            $line = $meta($taskName, $name, $key, $i) . $line;
                                        }
                                    } else {
                                        $taskDef[$key] = $meta($taskName, $name, $key) . $taskDef[$key];
                                    }
                                }
                            }
                            break;
                    }
                }
            }
        }
        return $cnf;
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

    /**
     * @param string|null $task
     * @return array
     */
    public function getMacros(string $task = null) :array
    {
        $config = $this->getConfig();
        return (null === $task) ? $config['macros'] : (isset($config['tasks'][$task]) ? $config['tasks'][$task]['macros'] : []);
    }
}
