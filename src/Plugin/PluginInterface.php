<?php
declare(strict_types=1);

namespace App\Plugin;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

interface PluginInterface
{
    public function appendConfiguration(ArrayNodeDefinition $rootNode) :void;
}