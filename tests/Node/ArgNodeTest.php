<?php
declare(strict_types=1);

namespace App\Tests\Node;

use App\Node\ArgNode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Input\InputArgument;

class ArgNodeTest extends TestCase
{

    public function provider()
    {
        return [
            [
                [

                ],
                [
                    'args' => []
                ]
            ],
            // mode normalization tests
            [
                [[
                    'args' => [
                        'bar' => 'foo'
                    ]
                ]],
                [
                    'args' => [
                        'bar' => [
                            'mode' => InputArgument::OPTIONAL,
                            'description' => '',
                            'default' => 'foo'
                        ]
                    ]
                ],
            ],
            [
                [[
                    'args' => [
                        'bar' => null
                    ]
                ]],
                [
                    'args' => [
                        'bar' => [
                            'mode' => InputArgument::REQUIRED,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'args' => [
                        'bar' => [
                            'mode' => [
                                'required',
                                'is_array'
                            ]
                        ]
                    ]
                ]],
                [
                    'args' => [
                        'bar' => [
                            'mode' => InputArgument::REQUIRED|InputArgument::IS_ARRAY,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'args' => [
                        'bar' => [
                            'mode' => 'required|is_array',
                        ]
                    ]
                ]],
                [
                    'args' => [
                        'bar' => [
                            'mode' => InputArgument::REQUIRED|InputArgument::IS_ARRAY,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'args' => [
                        'bar' => [
                            'mode' => 'required',
                        ]
                    ]
                ]],
                [
                    'args' => [
                        'bar' => [
                            'mode' => InputArgument::REQUIRED,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'args' => [
                        'bar' => [
                            'mode' => InputArgument::REQUIRED,
                        ],
                        'foo' => [
                            'mode' => 'is_array',
                        ],
                    ]
                ]],
                [
                    'args' => [
                        'bar' => [
                            'mode' => InputArgument::REQUIRED,
                            'description' => '',
                            'default' => null
                        ],
                        'foo' => [
                            'mode' => InputArgument::IS_ARRAY,
                            'description' => '',
                            'default' => null
                        ]
                    ]
                ],
            ],
            [
                [[
                    'args' => [
                        'bar' => [
                            'mode' => 'required|is_array',
                            'description' => 'some short description',
                            'default' => 'no-bar'
                        ],
                    ]
                ]],
                [
                    'args' => [
                        'bar' => [
                            'mode' => InputArgument::REQUIRED|InputArgument::IS_ARRAY,
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
    public function testArgNodeProcessor($a, $b)
    {
        $builder = new TreeBuilder('a');
        $builder->getRootNode()->append((new ArgNode())())->end();
        $processor = new Processor();
        $this->assertEquals($processor->process($builder->buildTree(), $a), $b);
    }

    public function testInvalidModeValue()
    {
        $this->expectException(InvalidDefinitionException::class);
        $builder = new TreeBuilder('a');
        $builder->getRootNode()->append((new ArgNode())())->end();
        $processor = new Processor();
        $processor->process($builder->buildTree(), [['args' => ['foo' => ['mode' => 'bar']]]]);
    }
}