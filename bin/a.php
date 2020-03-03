<?php

$loader = include __DIR__ . '/../vendor/autoload.php';

(new App\Application($loader))->run();