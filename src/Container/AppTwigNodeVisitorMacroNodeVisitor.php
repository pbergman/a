<?php
use App\AppConfig;
use App\Twig\NodeVisitor\MacroNodeVisitor;

return new MacroNodeVisitor($this->get(AppConfig::class));