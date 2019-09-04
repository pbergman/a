<?php

namespace App\Exception;

class PluginNotFoundException extends PluginException
{
    public function __construct(string $plugin, array $paths = [], $code = 0, \Throwable $previous = null)
    {
        if (!empty($paths)) {
            $paths = sprintf(' (searched in: "%s")', implode('", "', $paths));
        } else {
            $paths = null;
        }

        parent::__construct(sprintf('Could not find plugin "%s"%s', $plugin, $paths), $code, $previous);
    }
}