<?php
declare(strict_types=1);

namespace App;

use App\CommandLoader\CommandLoader;
use App\Plugin\PluginRegistry;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    /** @var InputInterface  */
    private $input;
    /** @var OutputInterface  */
    private $output;
    /** @var AppConfig */
    private $config;

    public function __construct(CommandLoader $loader, AppConfig $config, PluginRegistry $registry, InputInterface $input, OutputInterface $output)
    {
        parent::__construct(<<<EOV
         ___     
        /  /\    
       /  /::\   
      /  /:/\:\  
     /  /:/~/::\ 
    /__/:/ /:/\:\
    \  \:\/:/__\/
     \  \::/     
      \  \:\     
       \  \:\    
        \__\/
EOV
, '0.0.1');

        $this->setCommandLoader($loader);

        $this->config = $config;
        $this->input = $input;
        $this->output = $output;

//        $this->init($config, $registry);
    }

//    private function init(AppConfig $config, PluginRegistry $registry)
//    {
//        print_r($config->getMacros());exit;
//    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = $this->input;
        }
        if (null === $output) {
            $output = $this->output;
        }

        return parent::run($input, $output);
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(
            new InputOption('dump', 'd', InputOption::VALUE_NONE, 'Dump the script instead of executing')
        );
        $definition->addOption(
            new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'The location of the application config file', AppConfig::getDefaultConfigFile())
        );
        return $definition;
    }
}
