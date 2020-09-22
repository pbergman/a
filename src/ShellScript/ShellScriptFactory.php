<?php
namespace App\ShellScript;

use App\Events;
use App\Events\WriterEvent;
use App\Exception\ShellScriptFactoryException;
use App\Helper\ContextHelper;
use App\IO\WriterInterface;
use App\Plugin\PluginConfig;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Error\Error;

class ShellScriptFactory implements ShellScriptFactoryInterface
{
    private $twig;
    private $dispatcher;

    public function __construct(Environment $twig, EventDispatcherInterface $dispatcher)
    {
        $this->twig = $twig;
        $this->dispatcher = $dispatcher;
    }

    private function populateCxt(PluginConfig $cnf, array &$ctx)
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
    }

    /** @inheritDoc */
    public function create(WriterInterface $writer, string $name, PluginConfig $cnf, array $ctx = [])
    {
        $this->populateCxt($cnf, $ctx);
        $writer->writef("#!%s\n", $cnf->getConfig('shell', '/bin/bash'));

        try {

            if ($this->dispatcher->hasListeners(Events::PRE_TASK_WRITER)) {
                $this->dispatcher->dispatch(new WriterEvent($name, $writer, $cnf, $ctx), Events::PRE_TASK_WRITER);
            }

            if (($output = $this->twig->render($name, $ctx)) && !empty(trim($output))) {
                $writer->write($output);
            }

            if ($this->dispatcher->hasListeners(Events::POST_TASK_WRITER)) {
                $this->dispatcher->dispatch(new WriterEvent($name, $writer, $cnf, $ctx), Events::POST_TASK_WRITER);
            }

        } catch (Error $e) {
            throw new ShellScriptFactoryException('failed to create shell script', 0, $e);
        }
    }
}