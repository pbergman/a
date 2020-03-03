<?php
declare(strict_types=1);

namespace App\Config;

use App\Model\TaskEntry;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\Definition\VariableNode;

class TaskNode extends VariableNode
{
    /**
     * {@inheritdoc}
     */
    protected function validateType($value)
    {
        if (false === $value instanceof TaskEntry) {
            $ex = new InvalidTypeException(sprintf('Invalid type for path "%s". Expected "%s", but got %s.', $this->getPath(), TaskEntry::class, is_object($value) ? get_class($value) : \gettype($value)));
            if ($hint = $this->getInfo()) {
                $ex->addHint($hint);
            }
            $ex->setPath($this->getPath());
            throw $ex;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function isValueEmpty($value)
    {
        return null === $value || '' === (string)$value;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidPlaceholderTypes(): array
    {
        return ['object'];
    }
}
