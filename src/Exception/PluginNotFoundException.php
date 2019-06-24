<?php

namespace App\Exception;

use Throwable;

class PluginNotFoundException extends \RuntimeException implements AExceptionInterface
{
    public function __construct(string $plugin, array $paths, $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Could not find plugin "%s" (searched in: "%s")', $plugin, implode('", "', $paths)), $code, $previous);
    }
}