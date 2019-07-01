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

    public function getConfig() :array
    {
        if (null === $this->config) {
            $config = Yaml::parseFile($this->getAppConfigFile());

            if (isset($config['plugins'])) {
                foreach ($config['plugins'] as $name) {
                    $this->registerPlugin($name);
                }
                unset($config['plugins']);
            }

            $this->config = $this->processor->processConfiguration($this->builder, $this->resources->getConfigs([$config]));

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
        return $this->config;
    }

    public function registerPlugin(string $name) :void
    {
        $this->registry->register($name);
    }

    public function getGlobals() :array
    {
        if (isset($this->getConfig()['globals'])) {
            return $this->getConfig()['globals'];
        }
        return [];
    }

    public function getTasks() :array
    {
        if (isset($this->getConfig()['tasks'])) {
            return $this->getConfig()['tasks'];
        }
        return [];
    }
}
