<?php
declare(strict_types=1);

namespace App\Command;

use App\Helper\FileHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearCommand extends Command
{
    protected static $defaultName = 'cache:clear';

    protected function configure()
    {
        $this->setDescription('Clear all cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isVerbose = $output->isVerbose();
        foreach ($this->getAllFiles(FileHelper::getCacheDir()) as $file) {
            if ($isVerbose) {
                $output->writeln('> rm ' . $file);
            }
            unlink($file);
        }

        foreach ($this->getDirectories(FileHelper::getCacheDir()) as $file) {
            if ($isVerbose) {
                $output->writeln('> rmdir ' . $file);
            }
            rmdir($file);
        }
    }

    private function getDirectories(string $dir) :\Generator
    {
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if (('.' === $entry || '..' === $entry)) {
                    continue;
                }
                $file = FileHelper::joinPath($dir, $entry);
                if (is_dir($file)) {

                    foreach ($this->getDirectories($file) as $e) {
                        yield $e;
                    }

                    yield $file;
                }
            }
            closedir($handle);
        }
    }

    private function getAllFiles(string $dir) :\Generator
    {
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if (('.' === $entry || '..' === $entry)) {
                    continue;
                }
                $file = FileHelper::joinPath($dir, $entry);
                if (is_dir($file)) {
                    foreach ($this->getAllFiles($file) as $e) {
                        yield $e;
                    }
                }
                if (is_file($file)) {
                    yield $file;
                }
            }
            closedir($handle);
        }
    }
}
