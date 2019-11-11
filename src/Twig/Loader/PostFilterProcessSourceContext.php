<?php
declare(strict_types=1);

namespace App\Twig\Loader;

/**
 * This processor will check for post filters and wraps the text before in
 * an twig set `capture` block and print the var with that filter.
 *
 * so for example:
 *
 *      ls -la || filter_name
 *
 * will be replaced for:
 *
 *      {%- set filter_name_args -%}
 *          ls -la
 *      {%- endset -%}
 *      {% env_ssh_args | filter_name %}
 *
 * To ignore this the `nop` word can be used and this will just return
 * everything before the double vertical bar (||)
 */
class PostFilterProcessSourceContext implements ProcessSourceContextInterface
{
    public function process(string $context): string
    {
        return preg_replace_callback(
            '/^(?P<TEXT>.+)\s\|\|\s(?P<FILTER>[^($|\()]+)(?:\((?P<ARGS>[^\)]+)\))?\s?$/ms',
            function($m) {
                if ('nop' === $filter = trim($m['FILTER'])) {
                    return $m['TEXT'];
                }
                $setName = $filter . '_args';
                $filterName = $filter . ((isset($m['ARGS'])) ? '(' . $m['ARGS'] . ')' : '');
                return sprintf(
                    "{%%- set %1\$s -%%}\n  %2\$s\n{%%- endset -%%}\n{{ %1\$s | %3\$s }}\n",
                    $setName,
                    $m['TEXT'],
                    $filterName
                );
            },
            $context
        );
    }
}