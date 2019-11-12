<?php
namespace App\ShellScript;

use App\Plugin\PluginConfig;
use Twig\Environment;

class ShellScriptFactory implements ShellScriptFactoryInterface
{
    /** @var Environment */
    private $twig;
    /** @var bool */
    private $debug;

    public function __construct(Environment $twig, $debug = false)
    {
        $this->twig = $twig;
        $this->debug = $debug;
    }

    /** @inheritDoc */
    public function create($fd, string $name, PluginConfig $cnf, array $ctx = [])
    {

        fwrite($fd, sprintf("#!%s\n", $cnf->getConfig('shell', '/bin/bash')));
        fwrite($fd, sprintf("set -e%s\n", $this->debug ? 'x' : null));

        foreach ($cnf->getAllConfig() as $key => $value) {
            if (in_array($key, ['globals', 'macros', 'tasks'])) {
                continue;
            }
            if (false === array_key_exists($key, $ctx)) {
                $ctx[$key] = $value;
            }
        }

        $ctx['app.call'] = function($name, ...$args) use (&$ctx) {
            if (false === $func = $this->twig->getFunction($name)) {
                throw new \RuntimeException('No function exist for name ' . $name);
            }
            if ($func->needsContext()) {
                array_unshift($args, $ctx);
            }
            if ($func->needsEnvironment()) {
                array_unshift($args, $this->twig);
            }
            return $func->getCallable()(...$args);
        };

        $ctx['app.filter'] = function($name, ...$args) use (&$ctx) {
            if (false === $func = $this->twig->getFilter($name)) {
                throw new \RuntimeException('No filter exist for name ' . $name);
            }
            if ($func->needsContext()) {
                array_unshift($args, $ctx);
            }
            if ($func->needsEnvironment()) {
                array_unshift($args, $this->twig);
            }
            return $func->getCallable()(...$args);
        };

        if (($output = $this->twig->render($name, $ctx)) && !empty(trim($output))) {
            fwrite($fd, $output);
        }
    }
}