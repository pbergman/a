<?php
use App\Plugin\PluginRegistry;
use App\Twig\Loader\ChainedProcessSourceContext;
use App\Twig\Loader\ProcessSourceContextInterface;
use App\Twig\Loader\ShortLineProcessSourceContext;

$instance = $this->get(ChainedProcessSourceContext::class);

foreach ($this->get(PluginRegistry::class) as $plugin) {
    if ($plugin instanceof ProcessSourceContextInterface) {
        $instance->addProcessors($plugin);
    }
}

$instance->addProcessors($this->get(ShortLineProcessSourceContext::class));

return $instance;