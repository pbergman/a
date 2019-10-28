<?php
declare(strict_types=1);

namespace App\DependencyInjection;

use App\Exception\PluginNotFoundException;
use App\Plugin\PluginConfig;
use App\Plugin\PluginInterface;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Yaml\Parser;
use Twig\Extension\ExtensionInterface;

class AppExtension extends Extension
{
    private $loader;
    private $parser;
    private $plugins;

    public function __construct(ClassLoader $loader, Parser $parser, array $plugins)
    {
        $this->loader = $loader;
        $this->parser = $parser;
        $this->plugins = $plugins;
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $plugins = $this->initPlugins($container, $configs);
        $configuration = new Configuration($plugins);

        foreach($configs as $name => $cnf) {
            $configs[$name] = $this->processConfig((string)$name, $cnf);
        }

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/config'));
        $loader->load('services.xml');

        $container->setParameter('a.config', $config);
        $container
            ->getDefinition(PluginConfig::class)
            ->setArgument(0, '%a.config%');

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

    private function initPlugins(ContainerBuilder $container, &$configs) :array
    {
        $locator = $this->getPluginFileLocator();
        $locations = $classes = [];
        foreach ($this->plugins as $plugin) {
            $classes[$plugin] = $this->registerPlugin($plugin, $container, $locator, $locations, $configs);
        }
        $container->setParameter('a.plugin_location', $locations);
        $container->setParameter('a.plugins', $classes);
        return $classes;
    }

    private function registerPlugin(string $name, ContainerBuilder $container, FileLocator $locator, &$locations, &$configs) :string
    {
        $ns = $this->getPluginFQNS($name);
        try {
            $root = $locator->locate($name);
            $className = $ns . 'Plugin';
            $this->loader->addPsr4($ns, $root);
            if (class_exists($className) && is_a($className, PluginInterface::class, true)) {
                $definition = new Definition($className);
                $definition->setAutowired(true);
                $definition->addTag('a.plugin', ['name' => $name]);
                $definition->setPublic(true);
                if (is_a($className, ExtensionInterface::class, true)) {
                    $definition->addTag('twig.extension');
                }
                $container->setDefinition($className, $definition);
                $locations[$name] = [$root, $ns];
// @todo decide to move Extension to separated class
//
//                // add support for PluginExtension class
//                // that extents AbstractExtension
//                $extensionName =
//                if (class_exists($className . 'Extension') && is_a($className . 'Extension', ExtensionInterface::class, true)) {
//
//                    $definition = new Definition($className . 'Extension');
//                    $definition->setAutowired(true);
//                    $definition->addTag('twig.extension');
//                }
            }
            if (file_exists($file = $root . '/a.yaml')) {
                $configs[$name] = $this->parser->parseFile($file);
            }
            return $className;
        } catch (FileLocatorFileNotFoundException $e) {
            throw new PluginNotFoundException($name, $e->getPaths(), $e->getCode(), $e);
        }
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