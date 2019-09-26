<?php
use App\Command\CacheClearCommand;
use Psr\SimpleCache\CacheInterface;
use Twig\Environment;

return new CacheClearCommand(
    $this->get(CacheInterface::class),
    $this->get(Environment::class)
);