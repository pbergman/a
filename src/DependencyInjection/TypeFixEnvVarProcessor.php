<?php
declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class TypeFixEnvVarProcessor implements EnvVarProcessorInterface
{
    /** @inheritDoc */
    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        $value = $getEnv($name);
        return empty($value) ? false : $value;
    }

    /** @inheritDoc */
    public static function getProvidedTypes()
    {

//        'array', 'bool', 'float', 'int', 'string'

        return [
            'empty_is_false' => 'bool|string'
        ];
    }
}