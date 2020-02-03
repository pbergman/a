<?php
declare(strict_types=1);

namespace App\Model;

class TaskEntry
{
    /** @var string */
    private $exec;
    /** @var TaskMeta */
    private $meta;

    public static function newTaskEntry(string $exec, string $task, string  $plugin, string  $section = 'exec', $index = 0) :TaskEntry
    {
        $instance = new self();
        $instance->exec = $exec;
        $instance->meta = TaskMeta::newTaskMeta($task, $plugin, $section, $index);
        return $instance;
    }

    public static function __set_state($state)
    {
        $task = new self();
        $task->exec = $state['exec'];
        $task->meta = $state['meta'];
        return $task;
    }


    public function __toString()
    {
        return $this->exec;
    }

    public function getMeta() :TaskMeta
    {
        return $this->meta;
    }
}