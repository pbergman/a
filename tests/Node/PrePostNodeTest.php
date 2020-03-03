<?php
declare(strict_types=1);

namespace App\Tests\Node;

use App\Node\PrePostNode;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class PrePostNodeTest extends TestCase
{
    public function provider()
    {
        return [
            [
                [

                ],
                [
                    'pre' => []
                ]
            ],
            [
                [
                    [
                        'pre' => 'foo',
                    ]
                ],
                [
                    'pre' => [
                        'foo',
                    ]
                ]
            ],
            [
                [
                   [
                       'pre' => [
                            'foo',
                            'bar',
                        ]
                   ]
                ],
                [
                    'pre' => [
                        'foo',
                        'bar',
                    ]
                ]
            ],
            [
                [
                    [
                        'pre' => [
                            [
                                'exec' => 'foo',
                                'weight' => -10,
                            ],
                            [
                                'exec' => 'bar',
                                'weight' => -20,
                            ],
                        ]
                    ]
                ],
                [
                    'pre' => [
                        'bar',
                        'foo',
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testPrePostNodeProcessor($a, $b)
    {
        $builder = new TreeBuilder('a');
        $builder->getRootNode()->append((new PrePostNode())('pre'))->end();
        $processor = new Processor();
        $this->assertEquals($processor->process($builder->buildTree(), $a), $b);
    }
}