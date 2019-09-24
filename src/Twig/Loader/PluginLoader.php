<?php
declare(strict_types=1);

namespace App\Twig\Loader;

use App\Config\AppConfig;
use App\Exception\PluginNotFoundException;
use App\Exception\TaskNotExistException;
use App\Plugin\PluginCacheInterface;
use App\Plugin\PluginRegistry;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

final class PluginLoader implements LoaderInterface
{
    /** @var AppConfig  */
    private $config;
    /** @var PluginRegistry  */
    private $registry;
    /** @var ProcessSourceContextInterface */
    private $processor;

    public function __construct(AppConfig $config, PluginRegistry $registry, ProcessSourceContextInterface $processor)
    {
        $this->config = $config;
        $this->registry = $registry;
        $this->processor = $processor;
    }

    private function getCode(string $name) :?string
    {
        try {
            $ret = '';
            foreach ($this->config->getCode($name) as $line) {
                $ret .= $this->processor->process($line) . "\n'";
            }
            return $ret;
        } catch (TaskNotExistException $e) {
            throw new LoaderError(sprintf('Template "%s" is not defined.', $name), -1, null, $e);
        }

    }

    public function getSourceContext($name)
    {
        try {
            $ret = '';
            foreach ($this->config->getCode($name) as $line) {
                $ret .= $this->processor->process($line);
            }
            return new Source($ret, (string)$name);
        } catch (TaskNotExistException $e) {
            throw new LoaderError(sprintf('Template "%s" is not defined.', $name), -1, null, $e);
        }
    }

    public function exists($name)
    {
        try {
            $this->getCode((string) $name);
            return true;
        } catch (LoaderError $e) {
            return false;
        }
    }

    public function getCacheKey($name)
    {
        return $name . "::" . sha1($this->getCode((string)$name));
    }

    public function isFresh($name, $time)
    {
        $this->getCode((string)$name);
        $name = explode(':', $name)[0];

        if (null !== $plugin = $this->registry->getPlugin($name)) {
            if (($plugin instanceof PluginCacheInterface) && $plugin->isFresh($time)) {
                return true;
            }
        }

        try {
            // Only check the a.yaml file from the plugin because only when this
            // changes the cache is invalid. When an Plugin file is changed the
            // cache should not be directly changed.
            return $this->registry->getConfigResource($name)->isFresh($time);
        } catch (PluginNotFoundException $e) {
            return true;
        }

    }
}