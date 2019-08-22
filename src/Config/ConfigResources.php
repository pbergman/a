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

    public function getConfigs() :array
    {
        $data = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->getConfigResource() as $name => $resource) {
                $data[$name] = Yaml::parseFile($resource->getResource());
            }
        }

        return $data;
    }
}
