<?php
declare(strict_types=1);

namespace App\Tests\Node;

use App\Twig\Loader\ShortLineProcessSourceContext;
use PHPUnit\Framework\TestCase;

class ShortLineProcessSourceContextTest extends TestCase
{
    public function provider()
    {
        //'/@(?P<tag>verbatim|raw|include|extends|embed|block|use)\((?P<args>[^\)]+)?\)/ms',

        return [
            [
                // include
                [
                    '@include("foo.txt")',
                    '@include(foo.txt)',
                    '@include(\'foo.txt\')',
                ],
                [
                    '{% include \'foo.txt\' %}',
                    '{% include \'foo.txt\' %}',
                    '{% include \'foo.txt\' %}',
                ],
            ],
            [
                // verbatim|raw
                [
                    '@verbatim(foo\(txt\))',
                    '@verbatim(foo.txt)',
                    '@verbatim(
                    
                        some formatted long long long text 
                    
                    )',
                    '@raw(foo.txt)',
                    '@raw(
                    
                        some formatted long long long text 
                    
                    )',
                ],
                [
                    '{% verbatim %}foo(txt){% endverbatim %}',
                    '{% verbatim %}foo.txt{% endverbatim %}',
                    '{% verbatim %}
                    
                        some formatted long long long text 
                    
                    {% endverbatim %}',
                    '{% verbatim %}foo.txt{% endverbatim %}',
                    '{% verbatim %}
                    
                        some formatted long long long text 
                    
                    {% endverbatim %}',
                ],

            ]
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testShortLineProcessSourceContext($a, $b)
    {
        $processor = new ShortLineProcessSourceContext();

        for ($i = 0, $c = count($a); $i < $c; $i++) {
            $this->assertEquals($processor->process($a[$i]), $b[$i]);
        }
    }
}