<?php
declare(strict_types=1);

namespace App\Plugin;

use App\Exception\PluginException;
use App\Exception\PluginNotFoundException;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\Resource\FileResource;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;

class PluginRegistry implements \IteratorAggregate
{
    /** @var array */
    private $plugins = [];
    /** @var PluginFileLocator */
    private $locator;
    /** @var ClassLoader */
    private $loader;
    /** @var FileResource[] */
    private $resource = [];
    /** @var Environment */
    private $twig;

    /**
     * @param PluginFileLocator $locator
     * @param ClassLoader $loader
     */
    public function __construct(PluginFileLocator $locator, ClassLoader $loader, Environment $twig)
    {
        $this->loader = $loader;
        $this->locator = $locator;
        $this->twig = $twig;
    }

    private function getPluginNsPrefix(string $name) :string
    {
        return 'App\Plugin\\' . str_replace('_', '', ucwords(preg_replace('/[^A-Za-z_]/', '_', $name), '_')) .'\\';
    }

    public function isRegistered(string $name) :bool
    {
        return (isset($this->plugins[$name]) || isset($this->resource[$name]));
    }

    public function register(string $name) :void
    {
        if ($this->isRegistered($name)) {
            throw new PluginException('an plugin with name "' . $name .'" is allready registered.');
        }

        $prefix = $this->getPluginNsPrefix($name);

        try {
            $root = $this->locator->locate($name);
            $this->loader->addPsr4($prefix, $root);
            $className = $prefix . 'Plugin';
            if (class_exists($className) && is_a($className, PluginInterface::class, true)) {
                $instance = new $className();
                if ($instance instanceof ExtensionInterface) {
                    $this->twig->addExtension($instance);
                }
                $this->plugins[$name] = $instance;
            }
            if (file_exists($file = $root . '/a.yaml')) {
                $this->resource[$name] = new FileResource($file);
            }
        } catch (FileLocatorFileNotFoundException $e) {
            throw new PluginNotFoundException($name, $e->getPaths(), $e->getCode(), $e);
        }
    }

    public function getPlugin(string $name) :?PluginInterface
    {
        return (isset($this->plugins[$name])) ? $this->plugins[$name] : null;
    }

    /**
     * @return \Generator|PluginInterface[]
     */
    public function getIterator() :\Generator
    {
        foreach ($this->plugins as $name => $plugin) {
            yield $name => $plugin;
        }
    }

    /** @inheritDoc */
    public function getConfigResource(string $plugin = null)
    {
        if (null !== $plugin) {
            if (!isset($this->resource[$plugin])) {
                throw new PluginNotFoundException($plugin);
            }
            return $this->resource[$plugin];
        }
        return $this->resource;
    }
}