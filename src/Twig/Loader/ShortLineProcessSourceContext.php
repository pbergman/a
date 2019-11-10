<?php
declare(strict_types=1);

namespace App\Twig\Loader;

use Twig\Error\LoaderError;

class ShortLineProcessSourceContext implements ProcessSourceContextInterface
{

    public function process(string $context): string
    {
        return preg_replace_callback(
            '/@(?P<tag>verbatim|raw|include|extends|embed|block|use)\((?P<args>.*)?\)/ms',
            function($m) {
                switch ($m['tag']) {
                    // https://twig.symfony.com/doc/2.x/tags/verbatim.html
                    case 'verbatim':
                    case 'raw':
                        return $this->raw($m);
                        break;
                    // https://twig.symfony.com/doc/2.x/tags/include.html
                    case 'include':
                    case 'extends':
                    case 'use':
                        return $this->extends($m);
                        break;
                    // https://twig.symfony.com/doc/2.x/tags/embed.html
                    case 'embed':
                        return $this->embed($m);
                        break;
                    // https://twig.symfony.com/doc/2.x/tags/block.html
                    case 'block':
                        return $this->block($m);
                        break;

                }
                return $m[0];
            },
            $context
        );
    }

    private function block(array $matches)
    {
        if (!isset($matches['args'])) {
            $matches['args'] = '';
        }

        $args = $this->parseArgs($matches['args']);

        if ($args <= 0) {
            throw new LoaderError('Expecting a minimal of 1 arguments for \'block\' tag with: ' . $matches[0]);
        }

        $name = array_shift($args);

        return sprintf('{%% block %s %%}%s{%% endblock %%}', $name, implode(',', $args));
    }

    private function embed(array $matches)
    {
        if (!isset($matches['args'])) {
            $matches['args'] = '';
        }

        $args = $this->parseArgs($matches['args']);

        if ($args <= 0) {
            throw new LoaderError('Expecting a minimal of 2 arguments for \'embed\' tag with: ' . $matches[0]);
        }

        $name = array_shift($args);

        return sprintf('{%% embed "%s" %%}%s{%% endembed %%}', $name, implode(',', $args));

    }

    private function extends(array $matches) :string
    {
        if (!isset($matches['args'])) {
            throw new LoaderError('Missing required argument for include tag with: ' . $matches[0]);
        }

        $args = $this->parseArgs($matches['args']);
        $incl = '{% ' . $matches['tag'] . ' ' ;

        if (count($args) > 1) {
            $incl .= sprintf('[\'%s\'] %%}', implode('\', \'', $args));
        } else {
            $incl .= '\'' . $args[0] . '\' %}';
        }

        return $incl;
    }

    private function raw(array $matches) :string
    {
        if (empty($matches['args'])) {
            $matches['args'] = '';
        }

        return '{% verbatim %}' . $matches['args'] . '{% endverbatim %}';
    }

    private function unquote(string $str) :string
    {
        if (($str[0] === '\'' && substr($str, -1) === '\'') || ($str[0] === '"' && substr($str, -1) === '"')) {
            return substr($str, 1, -1);
        }
        return $str;
    }

    private function parseArgs(string $args) :array
    {
        $ret = [];

        foreach (array_map('trim', array_filter(explode(',', $args))) as $arg) {
            $ret[] = $this->unquote($arg);
        }

        return $ret;
    }
}