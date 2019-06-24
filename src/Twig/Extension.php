<?php

declare(strict_types=1);

namespace App\Twig;

use App\AppConfig;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class Extension extends AbstractExtension implements GlobalsInterface
{
    /** @var AppConfig */
    private $config;

    public function __construct(AppConfig $config)
    {
        $this->config = $config;
    }

    /** @inheritDoc */
    public function getGlobals()
    {
        return $this->config->getConfig();
    }
}