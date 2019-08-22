<?php

namespace App\Twig\Loader;

use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

final class PluginLoader implements LoaderInterface
{
    private $tasks  = [];

    const GROUP_INDEX = -1;

    public function __construct(array $templates = [])
    {
        $this->plugins = $templates;
    }

    public function addPlugin(string $exec, string $plugin, string  $task, string $group = 'exec')
    {
        $this->tasks[str_replace('.', ':', $task)][$plugin][$group][] = $exec;
    }

    private function getCode(string $name) :?string
    {
        static $cache;

        $name = str_replace('.', ':', $name);

        if (!$cache || false === array_key_exists($name, $cache)) {

            $error = function($name) :LoaderError {
                return new LoaderError(sprintf('Template "%s" is not defined.', $name));
            };

            $parts = explode('::', $name, 3);
            $index = null;

            if (isset($parts[2]) && false !== $pos = strpos($parts[2], '[')) {
                $index = (int)substr($parts[2], $pos+1, -1);
                $parts[2] = substr($parts[2], 0, $pos);
            }

            if (!isset($this->tasks[$parts[0]])) {
                throw $error($name);
            }

            switch (count($parts)) {
                // TASK
                case 1:
                    $tmpl = '';
                    foreach (array_keys($this->tasks[$parts[0]]) as $plugin) {
                        foreach(['pre', 'exec', 'post'] as $group) {
                            if (isset($this->tasks[$parts[0]][$plugin][$group])) {
                                for ($i = 0, $c =\count($this->tasks[$parts[0]][$plugin][$group]); $i < $c; $i++) {
                                    $tmpl .= sprintf("{%% include '%s::%s::%s[%d]' %%}\n", $parts[0], $plugin, $group, $i);
                                }
                            }
                        }
                    }
                    $cache[$name] = $tmpl;
                    break;
                // TASK::NAME
                case 2:
                    if (false === array_key_exists($parts[1], $this->tasks[$parts[0]])) {
                        throw $error($name);
                    }
                    $tmpl = '';
                    foreach(['pre', 'exec', 'post'] as $group) {
                        if (isset($this->tasks[$parts[0]][$parts[1]][$group])) {
                            for ($i = 0, $c =\count($this->tasks[$parts[0]][$parts[1]][$group]); $i < $c; $i++) {
                                $tmpl .= sprintf("{%% include '%s::%s::%s[%d]' %%}\n", $parts[0], $parts[1], $group, $i);
                            }
                        }
                    }
                    $cache[$name] = $tmpl;
                    break;
                // TASK::NAME::GROUP(INDEX)?
                case 3:
                    if (false === array_key_exists($parts[1], $this->tasks[$parts[0]]) || false === array_key_exists($parts[2], $this->tasks[$parts[0]][$parts[1]])) {
                        throw $error($name);
                    }
                    $tmpl = '';
                    if (null !== $index) {
                        if (!isset($this->tasks[$parts[0]][$parts[1]][$parts[2]][$index])) {
                            throw $error($name);
                        }
                        $tmpl = $this->tasks[$parts[0]][$parts[1]][$parts[2]][$index];
                    } else {
                        for ($i = 0, $c =\count($this->tasks[$parts[0]][$parts[1]][$parts[2]]); $i < $c; $i++) {
                            $tmpl .= sprintf("{%% include '%s::%s::%s[%d]' %%}\n", $parts[0], $parts[1], $parts[2], $i);
                        }
                    }
                    $cache[$name] = $tmpl;
                    break;
            }
        }

        return $cache[$name];
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