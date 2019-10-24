<?php
declare(strict_types=1);

namespace App\Twig\Loader;

use App\Exception\PluginNotFoundException;
use App\Exception\TaskNotExistException;
use App\Plugin\PluginConfig;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

final class PluginLoader implements LoaderInterface
{
    /** @var PluginConfig  */
    private $config;
    /** @var ProcessSourceContextInterface */
    private $processor;

    public function __construct(PluginConfig $config, ProcessSourceContextInterface $processor)
    {
        $this->config = $config;
        $this->processor = $processor;
    }

    private function getCode(string $name, $raw = false) :?string
    {
        try {
            $ret = '';
            foreach ($this->config->getTask($name) as $line) {
                $ret .= ($raw) ? $line : $this->processor->process($line);
            }
            return $ret;
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
        // @todo new implantation??

//        $this->getCode((string)$name);
//        $name = explode(':', $name)[0];

//        if (null !== $plugin = $this->registry->getPlugin($name)) {
//            if (($plugin instanceof PluginCacheInterface) && $plugin->isFresh($time)) {
//                return true;
//            }
//        }

        try {
            // Only check the a.yaml file from the plugin because only when this
            // changes the cache is invalid. When an Plugin file is changed the
            // cache should not be directly changed.
//            return $this->registry->getConfigResource($name)->isFresh($time);
            return true;
        } catch (PluginNotFoundException $e) {
            return true;
        }

    }
}