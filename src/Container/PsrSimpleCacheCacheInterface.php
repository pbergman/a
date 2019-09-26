<?php
use App\Cache\FileSystemCache;
use App\Cache\InMemoryCache;
use App\Config\AppConfigFile;
use App\Helper\FileHelper;
use Symfony\Component\Console\Input\InputInterface;

if ($this->get(InputInterface::class)->hasParameterOption(['--no-cache', '-N'], true)) {
    return new InMemoryCache();
} else {
    return new FileSystemCache(FileHelper::getCacheDir('app', sha1((string)$this->get(AppConfigFile::class)->getAppConfigFile())));
}