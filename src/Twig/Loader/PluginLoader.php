<?php

namespace App\Twig\Loader;

use App\AppConfig;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

final class PluginLoader implements LoaderInterface
{
    private $config  = [];

    public function __construct(AppConfig $config)
    {
        $this->config = $config;
    }

    private function realName(string $name) :string
    {
        return str_replace('.', ':', $name);
    }

    private function getCode(string $name) :?string
    {
        static $cache;

        $name = $this->realName($name);

        if (!$cache || false === array_key_exists($name, $cache)) {
            $parts = explode('::', $name, 2);
            $index = null;
            $tasks = $this->config->getTasks();

            if (!isset($tasks[$parts[0]])) {
                throw new LoaderError(sprintf('Template "%s" is not defined.', $name));
            }

            switch (count($parts)) {
                // TASK
                case 1:
                    $tmpl = '';
                    foreach(['pre', 'exec', 'post'] as $section) {
                        for ($i = 0, $c =\count($tasks[$parts[0]][$section]); $i < $c; $i++) {
                            $tmpl .= sprintf("{%% include '%s::%s[%d]' %%}\n", $parts[0], $section, $i);
                        }
                    }
                    $tmpl = substr($tmpl, 0 , -1);
                    break;
                case 2:
                    if (false !== $pos = strpos($parts[1], '[')) {
                        $index = (int)substr($parts[1], $pos+1, -1);
                        $parts[1] = substr($parts[1], 0, $pos);
                    }
                    if (!isset($index)) {
                        $tmpl = '';
                        for ($i = 0, $c =\count($tasks[$parts[0]][$parts[1]]); $i < $c; $i++) {
                            $tmpl .= sprintf("{%% include '%s::%s[%d]' %%}\n", $parts[0], $parts[1], $i);
                        }
                    } else {
                        $tmpl = $tasks[$parts[0]][$parts[1]][$index] . "\n";
                    }
                    $tmpl = substr($tmpl, 0 , -1);
                    break;
            }
            $cache[$name] = $tmpl;
        }
        return (string)$cache[$name];
    }

    public function getSourceContext($name)
    {
        return new Source($this->getCode((string) $name), (string)$name);
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
        return true;
    }
}