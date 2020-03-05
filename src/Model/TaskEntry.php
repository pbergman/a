<?php
declare(strict_types=1);

namespace App\Model;

class TaskEntry
{
    /** @var string|mixed */
    private $entry;
    /** @var TaskMeta */
    private $meta;

    public static function newTaskEntry($entry, string $task, string  $plugin, string  $section = 'exec', $index = 0) :TaskEntry
    {
        $instance = new self();
        $instance->entry = $entry;
        $instance->meta = TaskMeta::newTaskMeta($task, $plugin, $section, $index);
        return $instance;
    }

    public static function __set_state($state)
    {
        $task = new self();
        $task->entry = $state['entry'];
        $task->meta = $state['meta'];
        return $task;
    }

    public function __toString()
    {
        $ret = (string)$this->entry;

        if ("\n" !== substr($ret, -1)) {
            $ret .= "\n";
        }

        return $ret;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function getMeta() :TaskMeta
    {
        return $this->meta;
    }
}