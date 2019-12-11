<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\TwigFilter;

class UndefinedFilterCallback
{
    public function __invoke($name)
    {
        if (function_exists($name)) {
            return new TwigFilter($name, $name);
        }
        return false;
    }
}