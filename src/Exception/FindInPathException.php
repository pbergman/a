<?php
declare(strict_types=1);

namespace App\Exception;

class FindInPathException extends PluginException
{
    public function __construct(string $exec, array $paths)
    {
        parent::__construct(sprintf('could not find bin %s (looked in "%s")', $exec, implode('", "', $paths)));
    }
}
