<?php
use App\Plugin\PluginRegistry;
use App\Twig\Extension;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\LoaderInterface;

$instance = new Environment(
    $this->get(LoaderInterface::class),
    [
        'strict_variables' => 1,
        'autoescape' => false,
        'debug' => false
    ]
);

foreach ($this->get(PluginRegistry::class) as $plugin) {
    if ($plugin instanceof ExtensionInterface) {
        $instance->addExtension($plugin);
    }
}

$instance->addExtension($this->get(Extension::class));

//
//$profile = $this->get(\Twig\Profiler\Profile::class); //new \Twig\Profiler\Profile();
//$instance->addExtension(new \Twig\Extension\ProfilerExtension($profile));

//$twig->addExtension(new \Twig\Extension\ProfilerExtension($profile));
//
//$dumper = new \Twig\Profiler\Dumper\TextDumper();
//echo $dumper->dump($profile);


return $instance;
