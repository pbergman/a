<?php
declare(strict_types=1);

namespace App;

use App\CommandLoader\CommandLoader;
use App\Config\AppConfig;
use App\Config\AppConfigFile;
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
    /** @var AppConfigFile  */
    private $configFile;

    public function __construct(CommandLoader $loader, AppConfig $config, InputInterface $input, OutputInterface $output, AppConfigFile $configFile)
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

        $this->configFile = $configFile;
        $this->config = $config;
        $this->input = $input;
        $this->output = $output;
    }


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

        $definition->addOptions([
                new InputOption('no-cache', 'N', InputOption::VALUE_NONE, 'Disable the cache on runtime.'),
                $this->configFile->getInputOption(),
        ]);

        return $definition;
    }
}
