<?php
namespace App\ShellScript;

use App\AppConfig;
use Twig\Environment;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

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

//        $tmpl = '';
//
//        foreach ($cnf->getMacros() as $macro) {
//            $tmpl .= $macro ."\n";
//        }
//
//        $tmpl .= sprintf("{{ include '%s' }}\n", $name);
        $this->twig->addRuntimeLoader(new class() implements RuntimeLoaderInterface {

            /**
             * Creates the runtime implementation of a Twig element (filter/function/test).
             *
             * @param string $class A runtime class
             *
             * @return object|null The runtime instance or null if the loader does not know how to create the runtime for this class
             */
            public function load($class)
            {
                var_dump($class);exit;
            }
        });
        if (($output = $this->twig->render($name, $ctx)) && !empty($output)) {
            fwrite($fd, $output . "\n");
        }
    }
}