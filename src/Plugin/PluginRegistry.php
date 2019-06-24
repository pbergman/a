<?php
declare(strict_types=1);

namespace App\Plugin;

use App\Config\ConfigArragatorInterface;
use App\Exception\PluginNotFoundException;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\Resource\FileResource;

class PluginRegistry implements \IteratorAggregate, ConfigArragatorInterface
{
    /** @var array */
    private $plugins = [];
    /** @var PluginFileLocator */
    private $locator;
    /** @var ClassLoader */
    private $loader;
    /** @var FileResource[] */
    private $resource = [];

    /**
     * @param PluginFileLocator $locator
     * @param ClassLoader $loader
     */
    public function __construct(PluginFileLocator $locator, ClassLoader $loader)
    {
        $this->loader = $loader;
        $this->locator = $locator;
    }

    private function getPluginNsPrefix(string $name) :string
    {
        return 'App\Plugin\\' . str_replace('_', '', ucwords(preg_replace('/[^A-Za-z_]/', '_', $name), '_')) .'\\';
    }

    public function register(string $name)
    {
        $prefix = $this->getPluginNsPrefix($name);
        try {
            $root = $this->locator->locate($name);
            $this->loader->addPsr4($prefix, $root);
            $className = $prefix . 'Plugin';
            if (class_exists($className) && is_a($className, PluginInterface::class, true)) {
                $this->plugins[$name] = [$root, new $className()];
            }
            if (file_exists($file = $root . '/a.yaml')) {
                $this->resource[] = new FileResource($file);
            }
        } catch (FileLocatorFileNotFoundException $e) {
            throw new PluginNotFoundException($name, $e->getPaths(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        foreach ($this->plugins as $name => [, $plugin]) {
            yield $name => $plugin;
        }
    }

    /** @return \Symfony\Component\Config\Resource\FileResource[] */
    public function getConfigResource(): array
    {
        return $this->resource;
    }
}