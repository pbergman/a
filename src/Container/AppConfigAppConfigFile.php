<?php
use App\Config\AppConfigFile;
use Symfony\Component\Console\Input\InputInterface;

return new AppConfigFile(
    $this->get(InputInterface::class)
);