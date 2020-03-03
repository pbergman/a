<?php

declare(strict_types=1);

namespace App\Tests\Node;

use App\Node\TaskNode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class TaskNodeTest extends TestCase
{

    public function provider()
    {
        return [
            [
                [[
                    'tasks' => [
                        'foo' => 'bar'
                    ]
                ]],
                [
                    'tasks' => [
                        'foo' => [
                            'exec' => ['bar'],
                            'hidden' => false,
                            'description' => null,
                            'help' => null,
                            'args' => [],
                            'opts' => [],
                            'pre' => [],
                            'post' => [],
                        ]
                    ]
                ],
            ],
            [
                [[
                    'tasks' => [
                        'foo' => [
                            'exec' => 'bar',
                            'hidden' => true,
                        ]
                    ]
                ]],
                [
                    'tasks' => [
                        'foo' => [
                            'exec' => ['bar'],
                            'hidden' => true,
                            'description' => null,
                            'help' => null,
                            'args' => [],
                            'opts' => [],
                            'pre' => [],
                            'post' => [],
                        ]
                    ]
                ],
            ],
            [
                [[
                    'tasks' => [
                        'foo' => [
                            'exec' => ['bar', 'foo'],
                            'hidden' => true,
                        ]
                    ]
                ]],
                [
                    'tasks' => [
                        'foo' => [
                            'exec' => ['bar', 'foo'],
                            'hidden' => true,
                            'description' => null,
                            'help' => null,
                            'args' => [],
                            'opts' => [],
                            'pre' => [],
                            'post' => [],
                        ]
                    ]
                ],
            ]
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testTaskNodeProcessor($a, $b)
    {
        $builder = new TreeBuilder('a');
        $builder->getRootNode()->append((new TaskNode())())->end();
        $processor = new Processor();
        $this->assertEquals($processor->process($builder->buildTree(), $a), $b);
    }
}