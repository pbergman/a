<?php
declare(strict_types=1);

namespace App\Tests\Node;

use App\Node\OptNode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\InputOption;

class OptNodeTest extends TestCase
{

    public function provider()
    {
        return [
            [
                [

                ],
                [
                    'opts' => []
                ]
            ],
            // mode normalization tests
            [
                [[
                    'opts' => [
                        'bar' => 'foo'
                    ]
                ]],
                [
                    'opts' => [
                        'bar' => [
                            'mode' => InputOption::VALUE_REQUIRED,
                            'shortcut' => null,
                            'description' => '',
                            'default' => 'foo'
                        ]
                    ]
                ],
            ],
            [
                [[
                    'opts' => [
                        'bar' => null
                    ]
                ]],
                [
                    'opts' => [
                        'bar' => [
                            'mode' => InputOption::VALUE_NONE,
                            'shortcut' => null,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'opts' => [
                        'bar' => [
                            'mode' => [
                                'required',
                                'is_array'
                            ]
                        ]
                    ]
                ]],
                [
                    'opts' => [
                        'bar' => [
                            'mode' => InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,
                            'shortcut' => null,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'opts' => [
                        'bar' => [
                            'mode' => 'required|is_array',
                        ]
                    ]
                ]],
                [
                    'opts' => [
                        'bar' => [
                            'mode' => InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,
                            'shortcut' => null,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'opts' => [
                        'bar' => [
                            'mode' => 'required',
                        ]
                    ]
                ]],
                [
                    'opts' => [
                        'bar' => [
                            'mode' => InputOption::VALUE_REQUIRED,
                            'shortcut' => null,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'opts' => [
                        'bar' => [
                            'mode' => 'value_required',
                        ]
                    ]
                ]],
                [
                    'opts' => [
                        'bar' => [
                            'mode' => InputOption::VALUE_REQUIRED,
                            'shortcut' => null,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'opts' => [
                        'bar' => [
                            'mode' => InputOption::VALUE_REQUIRED,
                        ],
                        'foo' => [
                            'mode' => 'is_array',
                        ],
                    ]
                ]],
                [
                    'opts' => [
                        'bar' => [
                            'mode' => InputOption::VALUE_REQUIRED,
                            'shortcut' => null,
                            'description' => '',
                            'default' => null
                        ],
                        'foo' => [
                            'mode' => InputOption::VALUE_IS_ARRAY,
                            'shortcut' => null,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'opts' => [
                        'bar' => [
                            'mode' => 'required|is_array',
                            'shortcut' => 'b',
                            'description' => 'some short description',
                            'default' => 'no-bar'
                        ],
                    ]
                ]],
                [
                    'opts' => [
                        'bar' => [
                            'mode' => InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,
                            'shortcut' => 'b',
                            'description' => 'some short description',
                            'default' => 'no-bar'
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testOptNodeProcessor($a, $b)
    {
        $builder = new TreeBuilder('a');
        $builder->getRootNode()->append((new OptNode())())->end();
        $processor = new Processor();
        $this->assertEquals($processor->process($builder->buildTree(), $a), $b);
    }

    public function testInvalidModeValue()
    {
        $this->expectException(InvalidDefinitionException::class);
        $builder = new TreeBuilder('a');
        $builder->getRootNode()->append((new OptNode())())->end();
        $processor = new Processor();
        $processor->process($builder->buildTree(), [['opts' => ['foo' => ['mode' => 'bar']]]]);
    }
}