<?php
declare(strict_types=1);

namespace App\DependencyInjection;

use App\DependencyInjection\Dumper\XmlServiceDumper;
use App\Helper\FileHelper;
use App\Plugin\PluginConfig;
use App\Plugin\PluginInterface;
use App\Plugin\PluginRegistry;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Parser;

class AppExtension extends Extension
{
    /** @var ClassLoader  */
    private $loader;
    /** @var Parser  */
    private $parser;
    /** @var array  */
    private $plugins;
    /** @var string  */
    private $base;

    public function __construct(ClassLoader $loader, Parser $parser, array $plugins, string $base)
    {
        $this->loader = $loader;
        $this->parser = $parser;
        $this->plugins = $plugins;
        $this->base = $base;
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        if (false === is_file($file = FileHelper::joinPath($this->base, 'services.xml'))) {
            $writer = new XmlServiceDumper(dirname(__DIR__, 2));
            $plugins = $this->initPlugins($container, $configs, $writer);
            $writer->dump($file);
        } else {
            $plugins = $this->initPlugins($container, $configs);
        }

        $config = $this->getConfig($plugins, $configs);
        $loader = new XmlFileLoader($container, new FileLocator($this->base));
        $loader->load('services.xml');

        $registry = $container->getDefinition(PluginRegistry::class);

        foreach ($plugins as $name => $class) {
            $registry->addMethodCall('addPlugin', [$name, new Reference($class)]);
        }

        $container->setParameter('a.config', $config);
        $container
            ->getDefinition(PluginConfig::class)
            ->setArgument(0, '%a.config%');

    }

    private function getConfig(array $plugins, array $configs) :array
    {
        $configuration = new Configuration($plugins);

        foreach($configs as $name => $cnf) {
            $configs[$name] = $this->processConfig((string)$name, $cnf);
        }

        return $this->processConfiguration($configuration, $configs);
    }

    private function getPluginFQNS(string $name) :string
    {
        return 'App\Plugin\\' . str_replace('_', '', ucwords(preg_replace('/[^A-Za-z_]/', '_', $name), '_')) .'\\';
    }

    private function getPluginFileLocator() :FileLocator
    {
        $locations = [];

        foreach (explode(PATH_SEPARATOR, (string)getenv('A_PLUGIN_PATH')) as $path) {
            $locations[] = glob($path, GLOB_ONLYDIR|GLOB_BRACE);
        }

        return new FileLocator(array_merge(...$locations));
    }

    private function initPlugins(ContainerBuilder $container, &$configs, XmlServiceDumper $xml = null) :array
    {
        $locator = $this->getPluginFileLocator();
        $plugins = $extensions = [];

        foreach ($this->plugins as $plugin) {
            $location = $locator->locate($plugin);
            $namespace = $this->getPluginFQNS($plugin);
            $this->loader->addPsr4($namespace, $location);
            $extension = $namespace . 'Plugin';

            if (class_exists($extension) && is_a($extension, PluginInterface::class, true)) {
                $extensions[$plugin] = $extension;
            } else {
                $extension = null;
            }

            if (file_exists($file = $location . '/a.yaml')) {
                $configs[$plugin] = $this->parser->parseFile($file);
            }

            if (null !== $xml) {
                $xml->addPrototype($namespace,  $location . '/*');
            }

            $plugins[$plugin] = [
                'location' => $location,
                'namespace' => $namespace,
            ];
        }

        $container->setParameter('a.plugins', $plugins);
        return $extensions;
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
                                    foreach ($taskDef[$key] as $i => $line) {
                                        $taskDef[$key][$i] = $this->serialize($line, $taskName, $name, $key, $i);
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