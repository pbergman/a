<?php
declare(strict_types=1);

namespace App;

use App\Command\CommandLoader;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    private $input;

    public function __construct(CommandLoader $loader, InputInterface $input = null)
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
        $this->input = $input;
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return parent::run($this->input, $output);
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(
            new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'The location of the application config file', AppConfig::getDefaultConfigFile())
        );
        return $definition;
    }

}
