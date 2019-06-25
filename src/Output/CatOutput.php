<?php

namespace App\Output;

class CatOutput
{
    public function write(string $shell, string $name, array $task) :array
    {
        $out = "<(cat <<- _EOF_\n#!/bin/bash\n";


        foreach (['pre', 'exec', 'post'] as $group) {
            foreach ($task[$group] as $index => $line) {
                $out .= sprintf("# %s.%s[%d]\n%s\n", $name, $group, $index, str_replace("\n", "\n  ", $line));
            }
        }

        $out .= "_EOF_\n)\n";

        return [$out];
    }
}