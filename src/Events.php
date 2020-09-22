<?php
declare(strict_types=1);

namespace App;

class Events
{
    /**
     * Event will be dispatched before writing tasks and can be
     * used to write extra things to script
     *
     * @param \App\Events\WriterEvent
     */
    const PRE_TASK_WRITER   = 'pre.task.writer';

    /**
     * Event will be dispatched after writing tasks and can be
     * used to write extra things to script
     *
     * @param \App\Events\WriterEvent
     */
    const POST_TASK_WRITER  = 'post.task.writer';
}