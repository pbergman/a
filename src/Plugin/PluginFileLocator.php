<?php
declare(strict_types=1);

namespace App\Plugin;

use Symfony\Component\Config\FileLocator;

class PluginFileLocator extends FileLocator
{
    public function __construct(string $pattern)
    {
        $locations = [];

        foreach (explode(PATH_SEPARATOR, $pattern) as $path) {
            $locations = array_merge($locations, glob($path, GLOB_ONLYDIR|GLOB_BRACE));
        }

        parent::__construct($locations);
    }
}