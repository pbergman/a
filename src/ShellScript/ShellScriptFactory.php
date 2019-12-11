<?php
namespace App\ShellScript;

use App\Exception\ShellScriptFactoryException;
use App\Helper\ContextHelper;
use App\Plugin\PluginConfig;
use Twig\Environment;
use Twig\Error\Error;

class ShellScriptFactory implements ShellScriptFactoryInterface
{
    /** @var Environment */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /** @inheritDoc */
    public function create($fd, string $name, PluginConfig $cnf, array $ctx = [])
    {
        foreach ($cnf->getAllConfig() as $key => $value) {
            if (in_array($key, ['globals', 'macros', 'tasks'])) {
                continue;
            }
            if (false === array_key_exists($key, $ctx)) {
                $ctx[$key] = $value;
            }
        }

        $ctx['app.helper'] = new ContextHelper($this->twig, $ctx);

        fwrite($fd, sprintf("#!%s\n", $cnf->getConfig('shell', '/bin/bash')));

        try {
            if (null !== $header = $cnf->getConfig('header')) {
                fwrite($fd, $this->twig->render('conf.header', $ctx) . "\n");
            }

            if (($output = $this->twig->render($name, $ctx)) && !empty(trim($output))) {
                fwrite($fd, $output);
            }
        } catch (Error $e) {
            throw new ShellScriptFactoryException('failed to create shell script', 0, $e);
        }
    }
}