<?php
declare(strict_types=1);

namespace App;

use App\DependencyInjection\AppCompilerPass;
use App\DependencyInjection\AutoLoaderCompilerPass;
use App\DependencyInjection\ParamsCompilerPass;
use App\DependencyInjection\TwigCompilerPass;
use App\Helper\FileHelper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

function Container() :ContainerInterface {
    static $instance;
    if (!$instance) {
        $file = FileHelper::getCacheDir('container.php');
        if (file_exists($file)) {
            require_once $file;
            $instance = new Container();
        } else {
            $instance = new ContainerBuilder();
            $loader = new XmlFileLoader($instance, new FileLocator(__DIR__ . '/../config'));
            $loader->load('services.xml');
            $instance->addCompilerPass(new ParamsCompilerPass());
            $instance->addCompilerPass(new AppCompilerPass());
            $instance->addCompilerPass(new AutoLoaderCompilerPass());
            $instance->addCompilerPass(new TwigCompilerPass());
            $instance->compile(true);
            $dumper = new PhpDumper($instance);
            file_put_contents($file, $dumper->dump(['class' => Container::class]));
        }
    }
    return $instance;
}

//use App\Exception\ContainerException;
//use Psr\Container\ContainerInterface;
//
//class Container implements ContainerInterface
//{
//    private $registry;
//    private $loading;
//
//    public function __construct(...$objects)
//    {
//        foreach ($objects as $object) {
//            $this->registry[get_class($object)] = $object;
//        }
//    }
//
//    /** @inheritDoc\ */
//    public function get($id)
//    {
//        if (array_key_exists($id, $this->registry)) {
//            return $this->registry[$id];
//        }
//
//        $file = sprintf('./src/Container/%s.php', str_replace('\\', '', $id));
//
//        if (file_exists($file)) {
//
//            try {
//
//                if (isset($this->loading[$id])) {
//                    return $this->loading[$id];
//                }
//
//
//                if ((new \ReflectionClass($id))->isInterface()) {
//                    $this->loading[$id] = (eval(sprintf('
//return function(&$i) {
//    return new class($i) implements %s {
//        private $i;
//        public function __construct(&$i)
//        {
//            $this->i = &$i;
//        }
//        public function __call($name, $arguments)
//        {
//            return $this->i->$name(...$arguments);
//        }
//    };
//};
//            ', $id)))($this->registry[$id]);
//                } else {
//                    $this->loading[$id] = (eval(sprintf('
//return function(&$i) {
//    return new class($i) extends %s {
//        private $i;
//        public function __construct(&$i)
//        {
//            $this->i = &$i;
//        }
//        public function __call($name, $arguments)
//        {
//            return $this->i->$name(...$arguments);
//        }
//    };
//};
//            ', $id)))($this->registry[$id]);
//                }
//
//                if (($this->registry[$id] = require $file) && !$this->registry[$id] instanceof $id) {
//                    throw new ContainerException('failed to load \'' . $id . '\' from \'' . $file . '\'');
//                }
//
//            } finally {
//                unset($this->loading[$id]);
//            }
//
//            return $this->registry[$id];
//        }
//
//        return $this->registry[$id] = new $id();
//    }
//
//    /** @inheritDoc\ */
//    public function has($id)
//    {
//        return true;
//    }
//}
