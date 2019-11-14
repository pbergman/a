<?php
declare(strict_types=1);

namespace App\DependencyInjection;

use App\Exception\ConfigException;
use App\Helper\FileHelper;
use App\Plugin\PluginConfig;
use App\Plugin\PluginInterface;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
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
            $xml = new \DOMDocument('1.0', 'UTF-8');
            $xml->preserveWhiteSpace = false;
            $xml->formatOutput = true;
            $xml->load(dirname(__DIR__, 2) . '/config/services.xml');
            $plugins = $this->initPlugins($container, $configs, $xml);
            $xml->save($file);
        } else {
            $plugins = $this->initPlugins($container, $configs);
        }

        $config = $this->getConfig($plugins, $configs);
        $loader = new XmlFileLoader($container, new FileLocator($this->base));
        $loader->load('services.xml');

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

    private function initPlugins(ContainerBuilder $container, &$configs, \DOMDocument $xml = null) :array
    {
        $locator = $this->getPluginFileLocator();
        $plugins = $extensions = [];

        if (null !== $xml){
            if (null === $element = $xml->getElementsByTagName('prototype')->item(0)) {
                throw new ConfigException('invalid service template, could not find prototype element');
            }
            foreach ($element->attributes as $attribute) {
                if (('resource' === $attribute->name || 'exclude' === $attribute->name) && 0 === strpos($attribute->value, '..')) {
                    $attribute->value = str_replace('..', dirname(__DIR__, 2), $attribute->value);
                }
            }
        }

        foreach ($this->plugins as $plugin) {
            $location = $locator->locate($plugin);
            $namespace = $this->getPluginFQNS($plugin);
            $this->loader->addPsr4($namespace, $location);
            $extension = $namespace . 'Plugin';

            if (class_exists($extension) && is_a($extension, PluginInterface::class, true)) {
                $extensions[] = $extension;
            } else {
                $extension = null;
            }

            if (file_exists($file = $location . '/a.yaml')) {
                $configs[$plugin] = $this->parser->parseFile($file);
            }

            if (null !== $xml) {
                $attribute = $xml->createElement('prototype');
                $attribute->setAttribute('namespace', $namespace);
                $attribute->setAttribute('resource', $location . '/*');

                if (null !== $service = $xml->getElementsByTagName('services')->item(0)) {
                    $service->insertBefore($attribute, $element->nextSibling);
                } else {
                    throw new ConfigException('invalid service template, could not find services element');
                }
            }

            $plugins[$plugin] = [
                'location' => $location,
                'namespace' => $namespace,
                'extension' => $extension,
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