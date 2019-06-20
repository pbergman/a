<?php

namespace App\Config;

use Symfony\Component\Yaml\Yaml;

class ConfigResources
{
    private $providers;

    public function __construct(ConfigArragatorInterface ...$providers)
    {
        $this->providers = $providers;
    }

    public function getConfigs($append = []) :array
    {

        foreach ($this->providers as $provider) {
            foreach ($provider->getConfigResource() as $resource) {
                $append[] = Yaml::parseFile($resource->getResource());
            }
        }

        return $append;
    }
}
