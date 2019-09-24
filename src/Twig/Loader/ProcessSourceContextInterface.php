<?php
declare(strict_types=1);

namespace App\Twig\Loader;

interface ProcessSourceContextInterface
{
    /**
     * @param string $context
     * @return string
     * @throw LoaderError
     */
    public function process(string $context) :string;
}
