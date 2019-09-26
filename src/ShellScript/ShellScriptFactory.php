<?php
namespace App\ShellScript;

use App\Config\AppConfig;
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
    public function create($fd, string $name, AppConfig $cnf, array $ctx = [])
    {

        fwrite($fd, sprintf("#!%s\n", $cnf->getConfig('shell', '/bin/bash')));
        fwrite($fd, sprintf("set -e%s\n", $this->debug ? 'x' : null));

        $extra = $cnf->getConfig();
        unset($extra['globals'], $extra['macros'], $extra['tasks']);

        if (($output = $this->twig->render($name, array_merge($ctx, $extra))) && !empty($output)) {
            fwrite($fd, $output);
        }
    }
}