<?php
use App\Config\AppConfigFile;
use App\Twig\Extension;
use App\Helper\FileHelper;
use Symfony\Component\Console\Input\InputInterface;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

$input = $this->get(InputInterface::class);
$debug = 3 === (int) getenv('SHELL_VERBOSITY') || $input->hasParameterOption('-vvv', true);

if (false !== $cache = !$input->hasParameterOption(['--no-cache', '-N'], true)) {
    // get cache folder, should be something like ~/.cache/a/twig
    $cache = FileHelper::getCacheDir('twig', sha1((string)$this->get(AppConfigFile::class)->getAppConfigFile()));
    // try to create else disable cache because app should still
    // work so make an noop when check failed
    if (!is_dir($cache) && !mkdir($cache, 0700, true) && !is_dir($cache)) {
            $cache = false;
    }
}

$instance = new Environment(
    $this->get(LoaderInterface::class),
    [
        'strict_variables' => 1,
        'autoescape' => false,
        'auto_reload' => true,
        'debug' => $debug,
        'cache' => $cache,
    ]
);

$instance->addExtension($this->get(Extension::class));

return $instance;
