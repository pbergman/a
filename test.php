<?php


use App\Plugin\PluginRegistry;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

include_once 'vendor/autoload.php';

$content = file_get_contents('vendor/composer/autoload_real.php');

preg_match('/class (?P<class>ComposerAutoloader[^\s]+)/', $content, $m);


$builder = new ContainerBuilder();

$definitions = new Definition(ClassLoader::class);
$definitions->setFactory($m['class'] . '::getLoader');
$builder->setDefinition(ClassLoader::class, $definitions);

$loader = new XmlFileLoader($builder, new FileLocator(__DIR__ . '/src/Resource/config'));
$loader->load('services.xml');
$builder->compile();

$dumper = new PhpDumper($builder);

var_dump($dumper->dump());exit;
