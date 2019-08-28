<?php
use App\Twig\NodeVisitor\DebugNodeVisitor;
use Twig\Profiler\Profile;

return new DebugNodeVisitor($this->get(Profile::class));