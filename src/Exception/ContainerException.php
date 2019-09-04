<?php
namespace App\Exception;

use Psr\Container\ContainerExceptionInterface;

class ContainerException extends \RuntimeException implements AExceptionInterface, ContainerExceptionInterface
{
}