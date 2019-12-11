<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\TwigFunction;

class UndefinedFunctionCallback
{
    public function __invoke($name)
    {
        if (function_exists($name)) {
            return new TwigFunction($name, $name);
        }

        return false;
    }
}