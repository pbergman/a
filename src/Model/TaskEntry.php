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

    public static function __set_state($an_array)
    {
        // TODO: Implement __set_state() method.
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