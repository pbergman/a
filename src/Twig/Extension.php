<?php

declare(strict_types=1);

namespace App\Twig;

use App\AppConfig;
use App\Twig\NodeVisitor\DebugNodeVisitor;
use App\Twig\TokenParser\IncludeTokenParser;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Extension extends AbstractExtension implements GlobalsInterface
{
    /** @var AppConfig */
    private $config;
    /** @var OutputInterface */
    private $output;

    public function __construct(AppConfig $config, OutputInterface $output)
    {
        $this->config = $config;
        $this->output = $output;
    }

    public function getFunctions()
    {
        return [
            'background' => new TwigFunction('background', [$this, 'background']),
            'is_quiet' => new TwigFunction('is_quiet', [$this->output, 'isQuiet']),
            'is_verbose' => new TwigFunction('is_verbose', [$this->output, 'isVerbose']),
            'is_very_verbose' => new TwigFunction('is_very_verbose', [$this->output, 'isVeryVerbose']),
            'is_debug' => new TwigFunction('is_debug', [$this->output, 'isDebug']),
        ];
    }

    public function getFilters()
    {
        return [
            'background' => new TwigFilter('background', [$this, 'background']),
        ];
    }

    public function getNodeVisitors()
    {
        return [
            new DebugNodeVisitor(),
        ];
    }


    public function background($line, $name = null)
    {
        if ($name !== null) {
            return "$line &\nexport $name=$!";
        } else {
            return "$line &";
        }
    }


    /** @inheritDoc */
    public function getGlobals()
    {
        return $this->config->getConfig();
    }
}