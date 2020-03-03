<?php
declare(strict_types=1);

namespace App\Helper;

use App\Exception\ContextException;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ContextHelper
{
    /** @var Environment  */
    private $twig;
    /** @var array  */
    private $ctx;

    public function __construct(Environment $twig, array &$ctx)
    {
        $this->twig = $twig;
        $this->ctx = &$ctx;
    }

    public function __invoke($name, ...$args)
    {
        if (false === $func = $this->twig->getFunction($name)) {
            if (false === $func = $this->twig->getFilter($name)) {
                throw new ContextException('No function or filter exist by name ' . $name);
            }
        }

        return $this->call($func, $args);
    }

    public function function($name, ...$args)
    {
        if (false === $func = $this->twig->getFunction($name)) {
            throw new ContextException('No method exist with name ' . $name);
        }

        return $this->call($func, $args);
    }

    public function filter($name, ...$args)
    {
        if (false === $func = $this->twig->getFilter($name)) {
            throw new ContextException('No filter exist with name ' . $name);
        }

        return $this->call($func, $args);
    }

    public function __call($name, $arguments)
    {
        return $this($this->toUnderScore($name), ...$arguments);
    }

    private function toUnderScore(string $name) :string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $name)), '_');
    }

    /**
     * @param TwigFilter|TwigFunction $func
     * @param array $args
     * @return array
     */
    private function call($func, array $args) :array
    {
        if ($func->needsContext()) {
            array_unshift($args, $this->ctx);
        }

        if ($func->needsEnvironment()) {
            array_unshift($args, $this->twig);
        }

        return $func->getCallable()(...$args);
    }


}