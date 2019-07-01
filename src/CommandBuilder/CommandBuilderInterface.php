<?php
namespace App\CommandBuilder;

use Symfony\Component\Console\Command\Command;

interface CommandBuilderInterface
{
    public function getCommand(string $name) :Command;
}