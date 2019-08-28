<?php
use App\AppConfig;
use App\Twig\Extension;
use App\Twig\NodeVisitor\NodeVisitorContainer;
use Symfony\Component\Console\Output\OutputInterface;

return new Extension(
    $this->get(AppConfig::class),
    $this->get(OutputInterface::class),
    $this->get(NodeVisitorContainer::class)
);
