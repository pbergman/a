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

    public static function newTaskMeta(string $task, string  $plugin, string  $section = 'exec', $index = 0) :TaskMeta
    {
        $instance = new self();
        $instance->task = $task;
        $instance->plugin = $plugin;
        $instance->section = $section;
        $instance->index = $index;
        return $instance;
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
        return self::newTaskMeta(
            (string)$data['task'],
            (string)$data['plugin'],
            (string)$data['section'],
            (int)$data['index']
        );
    }
}
