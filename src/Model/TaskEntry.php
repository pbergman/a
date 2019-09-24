<?php
declare(strict_types=1);

namespace App\Model;

class TaskEntry
{
    /** @var string */
    private $exec;
    /** @var TaskMeta */
    private $meta;

    public function __construct(string $exec, string $task, string  $plugin, string  $section = 'exec', $index = 0)
    {
        $this->exec = $exec;
        $this->meta = new TaskMeta($task, $plugin, $section, $index);
    }

    public static function __set_state($state)
    {
        $task = (new \ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();
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