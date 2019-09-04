<?php
declare(strict_types=1);

namespace App\Model;

class TaskMeta
{
    /** @var string */
    private $task;
    /** @var string */
    private $plugin;
    /** @var string */
    private $section;
    /** @var int */
    private $index;

    public function __construct(string $task, string  $plugin, string  $section = 'exec', $index = 0)
    {
        $this->task = $task;
        $this->plugin = $plugin;
        $this->section = $section;
        $this->index = $index;
    }

    /**
     * @return string
     */
    public function getTask(): string
    {
        return $this->task;
    }

    /**
     * @return string
     */
    public function getPlugin(): string
    {
        return $this->plugin;
    }

    /**
     * @return string
     */
    public function getSection(): string
    {
        return $this->section;
    }

    /**
     * @return int
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    public static function __set_state($data)
    {
        return new self(
            (string)$data['task'],
            (string)$data['plugin'],
            (string)$data['section'],
            (int)$data['index']
        );
    }
}
