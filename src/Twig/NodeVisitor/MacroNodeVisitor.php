<?php
declare(strict_types=1);

namespace App\Twig\NodeVisitor;

use App\Plugin\PluginConfig;
use Twig\Environment;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;
use Twig\Source;

class MacroNodeVisitor extends AbstractNodeVisitor
{
    use MacroFormatTrait;

    /** @var PluginConfig  */
    private $config;
    /** @var bool  */
    private $enabled = true;

    public function __construct(PluginConfig $config)
    {
        $this->config = $config;
    }

    private function getMacros(Environment $twig, $name, Node ...$extra)
    {
        $macros = [];

        foreach ($this->config->getMacros() as $key => $macro) {
            $stream = $twig->tokenize(new Source($this->createMacros($key, $macro),  'macro[' . '::' .$key . ']'));
            $macros[] = $twig->parse($stream)->getNode('macros');
        }

        foreach ($this->config->getMacros($name) as $key => $macro) {
            $stream = $twig->tokenize(new Source($this->createMacros($key, $macro),  'macro[' . $name . '::' .$key . ']'));
            $macros[] = $twig->parse($stream)->getNode('macros');
        }

        foreach ($extra as $node) {
            $macros[] = $node;
        }

        return $macros;
    }

    protected function doEnterNode(Node $node, Environment $env)
    {
        if (!$this->enabled || !$node instanceof ModuleNode) {
            return $node;
        }

        try {
            $this->enabled = false;
            $node->setNode('macros', new Node($this->getMacros($env, $this->getTaskNameFormNode($node), $node->getNode('macros'))));
        } finally {
            $this->enabled = true;
        }

        return $node;
    }

    protected function doLeaveNode(Node $node, Environment $env)
    {
        return $node;
    }

    public function getPriority()
    {
        return 0;
    }
}
