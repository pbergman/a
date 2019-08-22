<?php
use App\Plugin\PluginRegistry;
use App\Twig\Extension;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\LoaderInterface;

$instance = new Environment($this->get(LoaderInterface::class), ['strict_variables' => 1, 'autoescape' => false, 'debug' => true]);
foreach ($this->get(PluginRegistry::class) as $plugin) {
    if ($plugin instanceof ExtensionInterface) {
        $instance->addExtension($plugin);
    }
}
$instance->addExtension($this->get(Extension::class));
return $instance;
