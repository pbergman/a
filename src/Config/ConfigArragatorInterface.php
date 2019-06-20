<?php

namespace App\Config;

interface ConfigArragatorInterface
{
    /** @return \Symfony\Component\Config\Resource\FileResource[] */
    public function getConfigResource() :array;
}
