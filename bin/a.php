<?php

use App\Container;
use App\Application;

$loader = include_once dirname(__FILE__) . '/../vendor/autoload.php';

(new Container($loader))->get(Application::class)->run();

//// A_PLUGIN_PATH
//$path = '/home/philip/workspace/php/lib/a-plugin/*/*:/home/philip/.config/composer/vendor/*/*';
//
//$locator = new FileLocator(array_filter(array_merge(...array_map('glob', explode(PATH_SEPARATOR, $path))), 'is_dir'));
//$file = $locator->locate('env/Plugin.php');
//$loader->addPsr4('App\Plugin\Env\\', dirname($file));
//
////$file = getcwd() . '/' . 'a.yml';
////var_dump($loader);exit;
//var_dump(new App\Plugin\Env\Plugin());exit;