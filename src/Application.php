<?php
declare(strict_types=1);

namespace App;

use App\CommandLoader\CommandLoader;
use App\Command\ConfigDumpReferenceCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    /** @var ContainerInterface  */
    private $container;

    public function __construct(ContainerInterface $container)
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

        $this->setCommandLoader($container->get(CommandLoader::class));
        $this->container = $container;
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = $this->container->get(InputInterface::class);
        }

        return parent::run($input, $output);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer() :ContainerInterface
    {
        return $this->container;
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
