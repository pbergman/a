<?php
declare(strict_types=1);

namespace App\Events;

use App\IO\WriterInterface;
use App\Plugin\PluginConfig;
use Symfony\Contracts\EventDispatcher\Event;

class WriterEvent extends Event
{
    private $writer;
    private $config;
    private $name;
    private $ctx;

    public function __construct(string $name, WriterInterface $writer, PluginConfig $cnf, array $ctx = [])
    {
        $this->writer = $writer;
        $this->config = $cnf;
        $this->name = $name;
        $this->ctx = $ctx;
    }

    public function getWriter(): WriterInterface
    {
        return $this->writer;
    }

    public function getConfig(): PluginConfig
    {
        return $this->config;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCtx(): array
    {
        return $this->ctx;
    }
}