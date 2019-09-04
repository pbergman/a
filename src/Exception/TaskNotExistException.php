<?php
namespace App\Exception;

class TaskNotExistException extends \RuntimeException implements AExceptionInterface
{
    public function __construct(string $task, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Could not find task "%s"', $task), $code, $previous);
    }
}
