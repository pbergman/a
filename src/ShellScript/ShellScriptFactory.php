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
            if ($cnf->hasConfig('header')) {
                fwrite($fd, $this->twig->render('conf.header', $ctx) . "\n");
            }
            if ([] !== $envs = $cnf->getEnvs($name)) {
                $out = "# this script will be run with the following envs:\n";
                foreach ($envs as $key => $value) {
                    $out .= '# ' . $key . '=' . $value . "\n";
                }
                fwrite($fd, $out);
            }
            if ([] !== $exports = $cnf->getExports($name)) {
                foreach ($exports as $key => $value) {
                    fwrite($fd, sprintf("export %s=%s\n", $key, $value));
                }
            }
            if (($output = $this->twig->render($name, $ctx)) && !empty(trim($output))) {
                fwrite($fd, $output);
            }
        } catch (Error $e) {
            throw new ShellScriptFactoryException('failed to create shell script', 0, $e);
        }
    }
}