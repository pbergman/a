<?php
declare(strict_types=1);

namespace App\DependencyInjection\Dumper;

use Symfony\Component\DependencyInjection\Dumper\PhpDumper as BaseDumper;

class PhpDumper extends BaseDumper
{
    public function dump(array $options = [])
    {
        return preg_replace_callback_array(
            [
                '/<?php[^(use)]+(use)/' => function($m) {
                    return $m[0] . " Composer\Autoload\ClassLoader;\nuse";
                },
                '/public function __construct(.+?\{.+?}\r?\n)/s' => function($m) {
                    $eoc = strrpos($m[1], '}');
                    $ret = 'public function __construct(ClassLoader $loader';
                    if ($m[1][1] !== ')') {
                        $ret .= ', ';
                    }
                    $ret .= substr($m[1], 1, $eoc-1);
                    $ret .= <<<'EOF'

        foreach($this->parameters['a.plugins'] as $info) {
            $loader->addPsr4($info['namespace'], $info['location']);
        }
    
EOF;
                    $ret .= substr($m[1], $eoc);
                    return $ret;
                }
            ],
            parent::dump($options)
        );
    }
}