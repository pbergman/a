<?php
declare(strict_types=1);

namespace App;

use App\Command\ConfigDumpReferenceCommand;
use App\Config\ConfigResources;
use App\Config\ConfigTreeBuilder;
use App\Command\CommandLoader;
use App\Plugin\PluginFileLocator;
use App\Plugin\PluginRegistry;
use App\Twig\Extension;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;

class Container implements ContainerInterface
{
    private $registry;

    public function __construct(...$objects)
    {
        foreach ($objects as $object) {
            $this->register($object);
        }
    }

    public function register($object) :void
    {
        if (is_object($object)) {
            $this->registry[get_class($object)] = $object;
        }

        if (is_array($object) && count($object) === 2 && is_object($object[1])) {
            $this->registry[$object[0]] = $object[1];
        }
    }

    public function get(string $name) :object
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
        return new PluginFileLocator((string)getenv('A_PLUGIN_PATH'));
    }

    private function AppConfigConfigResources() :ConfigResources
    {
        return new ConfigResources($this->get(PluginRegistry::class));
    }

    private function SymfonyComponentConsoleInputInputInterface() :InputInterface
    {
        return new ArgvInput();
    }

    private function AppCommandCommandLoader() :CommandLoader
    {
        return new CommandLoader($this);
    }

    private function AppApplication() :Application
    {
        return new Application($this);
    }

    private function AppAppConfig() :AppConfig
    {
        return new AppConfig($this->get(InputInterface::class), $this->get(PluginRegistry::class), $this->get(ConfigResources::class), $this->get(Processor::class), $this->get(ConfigTreeBuilder::class));
    }

    private function AppCommandConfigDumpReferenceCommand() :ConfigDumpReferenceCommand
    {
        return new ConfigDumpReferenceCommand($this->get(AppConfig::class), $this->get(ConfigTreeBuilder::class), $this->get(PluginRegistry::class));
    }

    private function TwigLoaderLoaderInterface() :LoaderInterface
    {
        $cnf = $this->get(AppConfig::class);
        $src = [];
        foreach ($cnf->getTasks() as  $name => $task) {
            foreach ($task['pre'] as $index => $line) {
                $src[$name . '.pre[' . $index . ']'] = $line;
            }
            foreach ($task['exec'] as $index => $line) {
                $src[$name . '.exec[' . $index . ']'] = $line;
            }
            foreach ($task['post'] as $index => $line) {
                $src[$name . '.post[' . $index . ']'] = $line;
            }
        }
        return new ArrayLoader($src);
    }

    private function AppTwigExtension() :Extension
    {
        return new Extension($this->get(AppConfig::class));
    }

    private function TwigEnvironment() :Environment
    {
        $env = new Environment($this->get(LoaderInterface::class), ['strict_variables' => 1, 'autoescape' => false]);
        foreach ($this->get(PluginRegistry::class) as $plugin) {
            if ($plugin instanceof ExtensionInterface) {
                $env->addExtension($plugin);
            }
        }
        $env->addExtension($this->get(Extension::class));
        return $env;
    }
}
