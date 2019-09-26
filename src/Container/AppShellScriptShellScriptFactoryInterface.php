<?php
use App\ShellScript\ShellScriptFactory;
use Symfony\Component\Console\Input\InputInterface;
use Twig\Environment;

$input = $this->get(InputInterface::class);
$debug = 3 === (int) getenv('SHELL_VERBOSITY') || $input->hasParameterOption('-vvv', true);

return new ShellScriptFactory($this->get(Environment::class), $debug);