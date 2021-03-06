<?php
declare(strict_types=1);

namespace App\Twig\Loader;

use App\Application;
use App\Exception\PluginNotFoundException;
use App\Exception\TaskNotExistException;
use App\Plugin\PluginCacheInterface;
use App\Plugin\PluginConfig;
use App\Plugin\PluginRegistry;
use Symfony\Component\Config\Resource\FileResource;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

final class PluginLoader implements LoaderInterface
{
    /** @var PluginConfig  */
    private $config;
    /** @var ProcessSourceContextInterface */
    private $processor;
    /** @var PluginRegistry */
    private $plugins;

    public function __construct(PluginConfig $config, ProcessSourceContextInterface $processor, PluginRegistry $plugins)
    {
        $this->plugins = $plugins;
        $this->config = $config;
        $this->processor = $processor;
    }

    private function getCode(string $name, $raw = false) :?string
    {
        try {

            if (0 === strpos($name, 'conf.')) {
                $lines = (array)$this->config->getConfig(substr($name, 5));
            } else {
                $lines = $this->config->getTaskCode($name);
            }

            if ($raw) {
                return implode("", $lines);
            } else {
                $ret = '';
                foreach ($lines as $line) {
                    $ret .= ($raw) ? $line : $this->processor->process($line);
                }
                return $ret;
            }
        } catch (TaskNotExistException $e) {
            throw new LoaderError(sprintf('Template "%s" is not defined.', $name), -1, null, $e);
        }
    }

    public function getSourceContext($name)
    {
        return new Source($this->getCode($name), (string)$name);
    }

    public function exists($name)
    {
        try {
            $this->getCode((string)$name, true);
            return true;
        } catch (LoaderError $e) {
            return false;
        }
    }

    public function getCacheKey($name)
    {
        return $name . '::' . sha1($this->getCode((string)$name, true));
    }

    public function isFresh($name, $time)
    {
        $name = explode(':', $name)[0];

        if (null !== $plugin = $this->plugins->getPlugin($name)) {
            if (($plugin instanceof PluginCacheInterface) && false === $plugin->isFresh($time)) {
                return false;
            }
        }

        try {
            // Only check the a.yaml file from the plugin because only when this
            // changes the cache is invalid. When an Plugin file is changed the
            // cache should not be directly changed.
            return (new FileResource(getenv(Application::A_CONFIG_FILE)))->isFresh($time);
        } catch (PluginNotFoundException $e) {
            return true;
        }
    }
}