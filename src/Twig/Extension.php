<?php
declare(strict_types=1);

namespace App\Twig;

use App\Plugin\PluginConfig;
use App\Twig\NodeVisitor\NodeVisitorContainer;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TwigFunction;

class Extension extends AbstractExtension implements GlobalsInterface
{
    /** @var PluginConfig */
    private $config;
    /** @var NodeVisitorInterface */
    private $nodeVisitors;

    public function __construct(PluginConfig $config, NodeVisitorContainer $nodeVisitors)
    {
        $this->config = $config;
        $this->nodeVisitors = $nodeVisitors;
    }

    public function getNodeVisitors()
    {
        return $this->nodeVisitors;
    }

    /** @inheritDoc */
    public function getGlobals()
    {
        return $this->config->getConfig('globals');
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('filepath_join', 'App\\Helper\\FileHelper::joinPath'),
            new TwigFunction('cwd', 'getcwd'),
            new TwigFunction('is_dir', 'is_dir'),
            new TwigFunction('is_file', 'is_file'),
            new TwigFunction(
                'arg',
                function($context, $key) {
                    return $context['input']->getArgument($key);
                },
                [
                    'needs_context' => true,
                ]
            ),
            new TwigFunction(
                'opt',
                function($context, $key) {
                    return $context['input']->getOption($key);
                },
                [
                    'needs_context' => true,
                ]
            ),
        ];
    }
}