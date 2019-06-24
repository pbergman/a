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

    public function getConfigs($merge = []) :array
    {

        $data = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->getConfigResource() as $resource) {
                $data[] = Yaml::parseFile($resource->getResource());
            }
        }

        return array_merge($data, $merge);
    }
}
