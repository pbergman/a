<?php
declare(strict_types=1);

namespace App;

use App\CommandLoader\CommandLoader;
use App\DependencyInjection\AppExtension;
use App\DependencyInjection\CompilerPass\CommandLoaderPass;
use App\DependencyInjection\CompilerPass\NodeVisitorContainerPass;
use App\DependencyInjection\CompilerPass\TwigCompilerPass;
use App\DependencyInjection\Dumper\PhpDumper;
use App\Exception\RuntimeException;
use App\Helper\FileHelper;
use Composer\Autoload\ClassLoader;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;


class Application extends BaseApplication
{
//    /** @var InputInterface  */
//    private $input;
//    /** @var OutputInterface  */
//    private $output;
//    /** @var AppConfig */
//    private $config;
//    /** @var AppConfigFile  */
//    private $configFile;

    /** @var ClassLoader */
    private $loader;
    /** @var ContainerInterface */
    private $container;

    public function __construct(ClassLoader $loader)
//    public function __construct(CommandLoader $loader, AppConfig $config, InputInterface $input, OutputInterface $output, AppConfigFile $configFile)
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

        $this->loader = $loader;

//        $this->setCommandLoader($loader);
//
//        $this->configFile = $configFile;
//        $this->config = $config;
//        $this->input = $input;
//        $this->output = $output;
    }


    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        $this->init($input);
        $this->setCommandLoader($this->container->get(CommandLoader::class));

        return parent::run($input, $output);
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOptions([
                new InputOption('no-cache', 'N', InputOption::VALUE_NONE, 'Disable the cache on runtime.'),
                new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'The location of the application config file', $this->getDefaultConfigFile())
        ]);

        return $definition;
    }

    private function getDefaultConfigFile() :string
    {
        return getcwd() . '/a.yaml';
    }

    private function getConfigFile(InputInterface $input) :string
    {
        return $input->getParameterOption(['--config', '-c'], $this->getDefaultConfigFile(), true);
    }

    private function setEnvs(bool $isCache, bool $isDebug, string $hash)
    {
        $params = [
            'A_CACHE_TWIG' => $this->geTwigCache($isCache, $hash),
            'A_CACHE' => $isCache,
            'A_DEBUG' => $isDebug,
        ];

        foreach ($params as $key => $value) {
            if (false === array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
            }
        }
    }

    private function init(InputInterface $input)
    {
        $file = $this->getConfigFile($input);
        $hash = substr(sha1_file($file), 0, 8);
        $cache = FileHelper::getCacheDir();
        $isCache = false === $input->hasParameterOption(['--no-cache', '-N'], true);
        $isDebug = 3 === (int)getenv('SHELL_VERBOSITY') || $input->hasParameterOption('-vvv', true);

        $this->setEnvs($isCache, $isDebug, $hash);

        if (!is_dir($cache)) {
            if (mkdir($cache, 0700, true) && !is_dir($cache)) {
                throw new RuntimeException('failed to create folder' . $cache);
            }
        }

        $cacheContainer = FileHelper::joinPath($cache, $hash . '.container.php');

        if (false === $isCache || false === file_exists($cacheContainer)) {

            $container = new ContainerBuilder();
            $parser = new Parser();
            $config = $parser->parseFile($file);

            if (isset($config['plugins'])) {
                $plugins = $config['plugins'];
//                $container->setParameter('a.plugins', $config['plugins']);
                unset($config['plugins']);
            }

            $extension = new AppExtension($this->loader, $parser, $plugins);
            $container->addCompilerPass(new RegisterEnvVarProcessorsPass());
            $container->addCompilerPass(new CommandLoaderPass());
            $container->addCompilerPass(new TwigCompilerPass());
            $container->addCompilerPass(new NodeVisitorContainerPass());
            $container->registerExtension($extension);
            $container->loadFromExtension($extension->getAlias());
            $container->prependExtensionConfig($extension->getAlias(), $config);

            if (false === $isCache) {
                $container->compile(true);
                $this->container = $container;
                return;
            }

            $container->compile();

            if ($isCache) {
                $dumper = new PhpDumper($container);
                file_put_contents($cacheContainer, $dumper->dump(['class' => 'AppContainer']));
            }
        }

        require_once $cacheContainer;
        $this->container = new \AppContainer($this->loader);
    }

    private function geTwigCache($cache, string $hash)
    {
        if (false === $cache) {
            return false;
        }

        $cache = FileHelper::getCacheDir('twig', $hash);

        // try to create else disable cache because app should still
        // work so make an noop when check failed
        if (!is_dir($cache) && !mkdir($cache, 0700, true) && !is_dir($cache)) {
            $cache = false;
        }

        return $cache;
    }
}
