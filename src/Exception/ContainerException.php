<?php
declare(strict_types=1);

namespace App\Exception;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \RuntimeException implements AExceptionInterface, ContainerExceptionInterface
{
}