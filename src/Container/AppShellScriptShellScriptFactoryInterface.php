<?php
use App\ShellScript\ShellScriptFactory;
use Twig\Environment;

return new ShellScriptFactory($this->get(Environment::class));