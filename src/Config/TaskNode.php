<?php
declare(strict_types=1);

namespace App\Config;

use App\Model\TaskEntry;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\ScalarNode;

class TaskNode extends ScalarNode
{
    protected function validateType($value)
    {
        if ($value instanceof TaskEntry) {
            $value = $value->getEntry();
        }

        parent::validateType($value);
    }
}
