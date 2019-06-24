<?php
declare(strict_types=1);

namespace App;

interface ContainerInterface
{
    public function register($object) :void;

    public function get(string $name) :object;
}