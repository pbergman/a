<?php
use App\Cache\FileSystemCache;
use App\Cache\InMemoryCache;
use App\Config\AppConfigFile;
use App\Helper\FileHelper;
use Symfony\Component\Console\Input\InputInterface;

if (false !== $this->get(InputInterface::class)->getParameterOption(['--no-cache', '-N'], false, true)) {
    return new InMemoryCache();
} else {
    return new FileSystemCache(FileHelper::getCacheDir('app', sha1((string)$this->get(AppConfigFile::class)->getAppConfigFile())));
}