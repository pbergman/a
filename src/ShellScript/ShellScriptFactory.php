<?php
namespace App\ShellScript;

use App\AppConfig;
use Twig\Environment;
use Twig\Extension\ProfilerExtension;

class ShellScriptFactory implements ShellScriptFactoryInterface
{
    /** @var Environment */
    private $twig;
    /** @var bool */
    private $debug;

    public function __construct(Environment $twig, $debug = true)
    {
        $this->twig = $twig;
        $this->debug = $debug;
    }

    /** @inheritDoc */
    public function create($fd, string $name, AppConfig $cnf, array $ctx = [])
    {
        fwrite($fd, "#!/bin/bash\n");
        fwrite($fd, sprintf("set -e%s\n", $this->debug ? 'x' : null));

        $extra = $cnf->getConfig();
        unset($extra['globals'], $extra['macros'], $extra['macros'], $extra['tasks']);

        if (($output = $this->twig->render($name, array_merge($ctx, $extra))) && !empty($output)) {
            fwrite($fd, $output . "\n");
        }
    }
}