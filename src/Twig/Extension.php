<?php
declare(strict_types=1);

namespace App\Twig;

use App\Plugin\PluginConfig;
use App\Twig\NodeVisitor\NodeVisitorContainer;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\NodeVisitor\NodeVisitorInterface;

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
}