<?php
declare(strict_types=1);

namespace App;

use App\CommandLoader\CommandLoader;
use App\DependencyInjection\AppExtension;
use App\DependencyInjection\CompilerPass\CommandLoaderPass;
use App\DependencyInjection\CompilerPass\NodeVisitorContainerPass;
use App\DependencyInjection\CompilerPass\TwigCompilerPass;
use App\DependencyInjection\Dumper\PhpDumper;
use App\Exception\PluginException;
use App\Exception\RuntimeException;
use App\Helper\FileHelper;
use Composer\Autoload\ClassLoader;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Compiler\RegisterEnvVarProcessorsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\Yaml\Parser;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;


class Application extends BaseApplication
{
    const A_CONFIG_FILE = 'A_CONFIG_FILE';
    /** @var ClassLoader */
    private $loader;
    /** @var ContainerInterface */
    private $container;
    /** @var array  */
    private $envKeysSet = [];

    public function __construct(ClassLoader $loader)
    {
        parent::__construct(ApplicationVersion::NAME, ApplicationVersion::VERSION);
        $this->loader = $loader;
    }

    public function getSetEnvKeys() :array
    {
        return $this->envKeysSet;
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
        $file = $input->getParameterOption(['--config', '-c'], $this->getDefaultConfigFile(), true);
        putenv(self::A_CONFIG_FILE . '='. $file);
        return $file;
    }

    private function setEnvs(bool $isCache, bool $isDebug, string $hash)
    {
        $params = [
            'A_CACHE_TWIG' => $this->geTwigCache($isCache, $hash),
            'A_CACHE' => $isCache,
            'A_DEBUG' => $isDebug,
        ];
        foreach ($params as $key => $value) {
            if (false === getenv($key)) {
                putenv($key. '='. $value);
                $this->envKeysSet[] = $key;
            }
        }
    }

    private function createHash(string $file) :string
    {
        $ctx = hash_init('sha1');
        hash_update($ctx, $file);
        hash_update($ctx, file_get_contents($file));
        return substr(hash_final($ctx), 0, 8);
    }

    private function init(InputInterface $input)
    {
        $file = $this->getConfigFile($input);
        $hash = $this->createHash($file);
        $cache = FileHelper::getCacheDir($hash);
        $isCache = false === $input->hasParameterOption(['--no-cache', '-N'], true);
        $isDebug = 3 === (int)getenv('SHELL_VERBOSITY') || $input->hasParameterOption('-vvv', true);

        $this->setEnvs($isCache, $isDebug, $hash);

        if (!is_dir($cache)) {
            if (mkdir($cache, 0700, true) && !is_dir($cache)) {
                throw new RuntimeException('failed to create folder' . $cache);
            }
        }

        $cacheContainer = $cache . '/container.php';

        if (false === $isCache || false === file_exists($cacheContainer)) {

            $container = new ContainerBuilder();
            $parser = new Parser();
            $config = $parser->parseFile($file) ?? [];
            $plugins = [];

            if (isset($config['plugins'])) {
                $plugins = $config['plugins'];
                unset($config['plugins']);
            }

            $this->resolveAbstractTasks($config);

            $extension = new AppExtension($this->loader, $parser, $plugins, $cache);
            $config = $extension->processConfig('_root', $config);

            $container->addCompilerPass(new RegisterEnvVarProcessorsPass());
            $container->addCompilerPass(new CommandLoaderPass());
            $container->addCompilerPass(new TwigCompilerPass());
            $container->addCompilerPass(new NodeVisitorContainerPass());
            $container->addCompilerPass(new RegisterListenersPass(EventDispatcherInterface::class), PassConfig::TYPE_BEFORE_REMOVING);
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

    private function resolveAbstractTasks(array &$config) :void
    {
        $abstracts = [];

        foreach ($config['tasks'] as $name => $task) {
            if (isset($task['abstract']) && (bool)$task['abstract']) {
                unset(
                    $config['tasks'][$name],
                    $task['abstract']
                );
                $abstracts[$name] = $task;
            }
        }

        foreach ($config['tasks'] as $name => &$task) {
            if (isset($task['extends'])) {
                $extends = (array)$task['extends'];
                unset($task['extends']);
                foreach ($extends as $e) {
                    if (false === array_key_exists($e, $abstracts)) {
                        throw new PluginException(sprintf('Task "%s" extends non existing abstract task "%s"', $name, $e));
                    }
                    $this->merge($task, $abstracts[$e]);
                }
            }
        }

    }

    private function merge(array &$a, array $b) :void
    {
        foreach ($b as $name => $value) {
            if (!isset($a[$name])) {
                $a[$name] = $b[$name];
                continue;
            }
            if (is_array($value) && [] !== $value) {
                if (array_keys($value) === range(0, count($value) - 1)) {
                    // sequential
                    $a[$name] = array_merge($a[$name], $b[$name]);
                } else {
                    $this->merge($a[$name], $b[$name]);
                }
            }
        }
    }

    private function geTwigCache($cache, string $hash)
    {
        if (false === $cache) {
            return false;
        }

        $cache = FileHelper::getCacheDir($hash, 'twig');

        // try to create else disable cache because app should still
        // work so make an noop when check failed
        if (!is_dir($cache) && !mkdir($cache, 0700, true) && !is_dir($cache)) {
            $cache = false;
        }

        return $cache;
    }
}
