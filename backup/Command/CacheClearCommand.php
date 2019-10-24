<?php
declare(strict_types=1);

namespace App\Command;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

class CacheClearCommand extends Command
{
    /** @var array|callable[]  */
    private $cache;

    protected static $defaultName = 'cache:clear';

    public function __construct(Environment $twig)
    {
        parent::__construct();

        $this->cache = [
            'twig' => function() use ($twig) {
                if (false !== $conf = $twig->getCache()) {
                    foreach (glob($conf . '/*/*') as $file) {
                        unlink($file);
                    }
                    foreach (glob($conf . '/*', GLOB_ONLYDIR) as $file) {
                        rmdir($file);
                    }
                    rmdir($conf);
                }
            },
//            'app' => [$cache, 'clear']
        ];
    }

    protected function configure()
    {
        $this
            ->setDescription('Dump the merged config.')
            ->addOption('pool', 'p', InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'the pool to clear', ['twig', 'app']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        foreach ($input->getOption('pool') as $name) {
            if (!isset($this->cache[$name])) {
                throw new \InvalidArgumentException('No cache pool defined for ' . $name);
            }
            $this->cache[$name]();
        }
        $output->writeln('cache cleared');
    }
}