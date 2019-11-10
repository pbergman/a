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

        $extra = $cnf->getAllConfig();
        unset($extra['globals'], $extra['macros'], $extra['tasks']);

        if (($output = $this->twig->render($name, array_merge($ctx, $extra))) && !empty(trim($output))) {
            fwrite($fd, $output);
        }
    }
}