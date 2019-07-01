<?php
declare(strict_types=1);

namespace App\Helper;

use App\Exception\FindInPathException;

class FileHelper
{
    public static function findInPath(string $exec) :string
    {
        static $paths;

        if (!$paths) {
            $paths = explode(PATH_SEPARATOR, getenv('PATH'));
        }

        foreach ($paths as $path) {
            if (is_file($file = self::joinPath($path, $exec))) {
                return $file;
            }
        }

        throw new FindInPathException($exec, $paths);
    }

    public static function joinPath(string ...$elem) :string
    {
        return preg_replace('/'. preg_quote(DIRECTORY_SEPARATOR, '/').'{2,}/', DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $elem));
    }
}