<?php

require_once "vendor/autoload.php";

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Console\Command\Command;

class YamlUserLoader extends FileLoader
{
    /** @var TreeBuilder */
    private $builder;
    private $ret;

    public function __construct(FileLocatorInterface $locator, TreeBuilder $builder, array &$ret)
    {
        parent::__construct($locator);
        $this->builder = $builder;
        $this->ret = &$ret;
    }


    public function load($resource, $type = null)
    {
        $values = [];

        foreach ($this->locator->locate($resource, null, false) as $file) {
            $values[] = Yaml::parse(file_get_contents($file));

        }

        $processor = new \Symfony\Component\Config\Definition\Processor();
        $this->ret = $processor->process($this->builder->buildTree(), $values);

//        print_r($result );exit;

        // ... handle the config values

        // maybe import some other resource:

        // $this->import('extra_users.yaml');
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yaml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}

$args = function() {
    $node = new ArrayNodeDefinition('args');
    $node
        ->defaultValue([])
        ->useAttributeAsKey('name')
        ->arrayPrototype()
            ->children()
                ->scalarNode('name')->end()
                ->scalarNode('mode')
                ->beforeNormalization()
                    ->ifString()
                        ->then(function($v) {
                            $mode = 0;
                            foreach (array_map('trim', explode('|', $v)) as $part) {
                                $const = 'Symfony\\Component\\Console\\Input\\InputArgument::' . strtoupper($part);
                                if (!defined($const)) {
                                    throw new InvalidDefinitionException(
                                        'unexpected value "' . $part . '" expected "required", "optional" or "is_array" (or joined variation with an | delimiter, for example: required|is_array)'
                                    );
                                }
                                $mode |= constant($const);
                            }
                            return $mode;
                         })
                    ->end()
        ->defaultValue(null)
                ->end()
                ->scalarNode('description')->defaultValue('')->end()
                ->scalarNode('default')->defaultNull()->end()
            ->end()
        ->end();
    return $node;

};
use Symfony\Component\Console\Input\InputOption;

$opts = function() {
    $node =  new ArrayNodeDefinition('opts');
    $node
        ->defaultValue([])
        ->useAttributeAsKey('name')
        ->arrayPrototype()
            ->beforeNormalization()
                ->ifString()
                ->then(function($v) {
                    return ['default' => $v, 'mode' => InputOption::VALUE_REQUIRED];
                })
            ->end()
            ->beforeNormalization()
                ->ifEmpty()
                ->then(function() {
                    return ['mode' => InputOption::VALUE_NONE];
                })
            ->end()
            ->children()
                ->scalarNode('name')->end()
                ->scalarNode('shortcut')->defaultNull()->end()
                ->scalarNode('mode')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function($v) {
                            $mode = 0;
                            foreach (array_map('trim', explode('|', $v)) as $part) {
                                $name = strtoupper($part);
                                if (0 !== strpos($name, 'VALUE_')) {
                                    $name = 'VALUE_' . $name;
                                }
                                $const = InputOption::class . '::' . $name;
                                if (!defined($const)) {
                                    throw new InvalidDefinitionException(
                                        'unexpected value "' . $part . '" expected  "none", "required", "optional" or "is_array" (or joined variation with an | delimiter, for example: required|is_array)'
                                    );
                                }
                                $mode |= constant($const);
                            }
                            return $mode;
                        })
                    ->end()
                    ->defaultValue(null)
                ->end()
                ->scalarNode('description')->defaultValue('')->end()
                ->scalarNode('default')->defaultNull()->end()
            ->end()
        ->end();
    return $node;

};

$tasks = function() use ($args, $opts) {
    $node = new ArrayNodeDefinition('tasks');
    $node
        ->useAttributeAsKey('name')
        ->arrayPrototype()
            ->beforeNormalization()
                ->ifTrue(function($v){
                    return is_string($v);
                })
                ->then(function($v) {
                    return ['exec' => $v];
                })
            ->end()
            ->children()
                ->scalarNode('name')->end()
                ->booleanNode('hidden')->defaultFalse()->end()
                ->append($args())
                ->append($opts())
                ->arrayNode('exec')
                    ->beforeNormalization()
                        ->ifString()
                            ->castToArray()
                        ->end()
                        ->performNoDeepMerging()
                        ->prototype('scalar')->end()
                        ->defaultValue(array())
                    ->end()
                ->end()
            ->end()
        ->end();
    return $node;
};

$builder = new TreeBuilder('a');
$builder
    ->getRootNode()
        ->children()
        ->arrayNode('globals')
            ->defaultValue([])
            ->variablePrototype()->end()
        ->end()
        ->append($tasks())
    ->end();

$data = [];
$locator = new YamlUserLoader(new FileLocator([".", "./test"]), $builder, $data);
$locator->load("a.yaml");


$class = function($name, array &$task) {
    return new class($name, $task) extends Command{

        private $task;

        public function __construct(string $name, array &$cnf)
        {
            $this->task = $cnf;
            parent::__construct($name);
        }

        protected function configure()
        {
            foreach ($this->task['opts'] as $name => $opt) {
                $this->addOption($name, $opt['shortcut'], $opt['mode'], $opt['description'], $opt['default']);
            }
            foreach ($this->task['args'] as $name => $arg) {
                $this->addArgument($name, $arg['mode'], $arg['description'], $arg['default']);
            }
            if ($this->task['hidden']) {
                $this->setHidden(true);
            }
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            var_dump($input->getArguments(), $input->getOptions());
        }
    };
};


use Symfony\Component\Console\Application;

$application = new Application();

foreach ($data['tasks'] as $name => $config) {
    $application->add($class($name, $config));
}

$application->run();


//$loaderResolver = new LoaderResolver([new YamlUserLoader($locator)]);
//$delegatingLoader = new DelegatingLoader($loaderResolver);

// YamlUserLoader is used to load this resource because it supports
// files with the '.yaml' extension
//$a = $delegatingLoader->load('a.yaml');

