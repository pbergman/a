<?php

use App\Application;
use function App\Container;

include_once __DIR__ . '/../vendor/autoload.php';

(Container())->get(Application::class)->run();

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