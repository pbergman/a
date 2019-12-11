<?php
declare(strict_types=1);

namespace App\Twig\NodeVisitor;

use Twig\Node\Node;

trait MacroFormatTrait
{
    private function getTaskName(string $name) :string
    {
        return explode('::', $name)[0];
    }

    private function getTaskNameFormNode(Node $node) :string
    {
        return $this->getTaskName($node->getTemplateName() ?? '');
    }

    private function createMacros($name, $data) :string
    {
        static $cache;

        if (!$cache || !array_key_exists($name, $cache)) {
            if (0 === strpos(trim($data['code']), '{% macro')) {
                $cache[$name] = $data['code'];
            } else {
                if ("\n" === substr($data['code'], -1)) {
                    $data['code'] = substr($data['code'], 0, -1);
                }
                $cache[$name] = sprintf(
                    "{%%- macro %s(%s) -%%}\n%s\n{%%- endmacro -%%}",
                    $name,
                    implode(", ", $data['args']),
                    $data['code']
                );
            }
        }

        return $cache[$name];
    }
}