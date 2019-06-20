<?php

namespace App;

use App\Config\ConfigResources;
use App\Config\ConfigTreeBuilder;
use App\Console\CommandLoader;
use App\Plugin\PluginFileLocator;
use App\Plugin\PluginRegistry;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;

class Container
{
    private $registry;

    public function __construct(...$objects)
    {
        foreach ($objects as $object) {
            $this->register($object);
        }
    }

    public function register($object)
    {
        if (is_object($object)) {
            $this->registry[get_class($object)] = $object;
        }

        if (is_array($object) && count($object) === 2 && is_object($object[1])) {
            $this->registry[$object[0]] = $object[1];
        }
    }

    public function get(string $name)
    {
        if (isset($this->registry[$name])) {
            return $this->registry[$name];
        }

        $method = str_replace('\\', '', $name);

        if (is_callable([$this, $method])) {
            return $this->registry[$name] = $this->$method();
        }

        return $this->registry[$name] = new $name();
    }

    private function AppConfigConfigTreeBuilder() :ConfigTreeBuilder
    {
        return new ConfigTreeBuilder($this->get(PluginRegistry::class));
    }

    private function AppPluginPluginRegistry() :PluginRegistry
    {
        return new PluginRegistry($this->get(PluginFileLocator::class), $this->get(ClassLoader::class));
    }

    private function AppPluginPluginFileLocator() :PluginFileLocator
    {
        return new PluginFileLocator(getenv('A_PLUGIN_PATH'));
    }

    private function AppConfigConfigResources() :ConfigResources
    {
        return new ConfigResources($this->get(PluginRegistry::class));
    }

    private function SymfonyComponentConsoleInputInputInterface() :InputInterface
    {
        return new ArgvInput();
    }

    private function AppConsoleCommandLoader() :CommandLoader
    {
        return new CommandLoader($this->get(AppConfig::class));
    }

    private function AppApplication() :Application
    {
        return new Application($this->get(CommandLoader::class), $this->get(InputInterface::class));
    }

    private function AppAppConfig() :AppConfig
    {
        return new AppConfig($this->get(InputInterface::class), $this->get(PluginRegistry::class), $this->get(ConfigResources::class), $this->get(Processor::class), $this->get(ConfigTreeBuilder::class));
    }
}
