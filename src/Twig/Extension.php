<?php

declare(strict_types=1);

namespace App\Twig;

use App\AppConfig;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Extension extends AbstractExtension implements GlobalsInterface
{
    /** @var AppConfig */
    private $config;

    public function __construct(AppConfig $config)
    {
        $this->config = $config;
    }

    public function getFunctions()
    {
        return [
            'background' => new TwigFunction('background', [$this, 'background']),
        ];
    }

    public function getFilters()
    {
        return [
            'background' => new TwigFilter('background', [$this, 'background']),
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