<?php

namespace App\Twig\NodeVisitor;

use App\Twig\Node\MacroNode;
use App\Twig\Node\DebugNode;
use Twig\Environment;
use Twig\ExpressionParser;
use Twig\Lexer;
use Twig\Node\BlockNode;
use Twig\Node\BodyNode;
use Twig\Node\IncludeNode;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\NodeVisitor\AbstractNodeVisitor;
use Twig\Parser;
use Twig\Profiler\Node\EnterProfileNode;
use Twig\Profiler\Node\LeaveProfileNode;
use Twig\Profiler\Profile;
use Twig\Source;
use Twig\Token;
use Twig\TokenParser\MacroTokenParser;

class DebugNodeVisitor extends AbstractNodeVisitor
{
    private $stack = [];
    private $c = 0;
    private $profile;
    private $inmodule = false;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }


    protected function doEnterNode(Node $node, Environment $env)
    {



        if ($node instanceof ModuleNode) {

//            $this->inmodule = true;

//            $this->stack[] = $node->getTemplateName();
//            $node->setNode('macros', new Node(array_merge($this->macros, $node->getNode('macros'))));
//
////            $tmpl = $env->createTemplate('{% macro vf() %}cccc{% endmacro %}', 'macro_');
//////            var_dump($tmpl );exit;
////////            var_dump($env->getTokenParsers());exit;
//            $parser = new Parser($env);
//////            $parser->
//            $parser->subparse(function(Token $token) {
//                return $token->test('endmacro');
//            }, true);
////
//            // {% if is_debug() %}vvv{% elseif is_very_verbose() %}vv{% elseif is_verbose() %}v{% else %}q{% endif %}
//
//            $Lexer = new Lexer($env);
//            $stream = $Lexer->tokenize(new Source('{% macro verbose_flags() %}dddd{% endmacro %}', 'verbose_flags'));
//            $parser = new Parser($env);
//
//            $ref = new \ReflectionProperty($parser, 'stream');
//            $ref->setAccessible(true);
//            $ref->setValue($parser, $stream);
//
//            $ep = new ExpressionParser($parser, $env);
//
//            $arguments = $ep->parseArguments(true, true);
//            var_dump($arguments);exit;
//
//            $mtp = new MacroTokenParser();
//            $mtp->setParser($parser);
//            $mtp->parse(new Token(Token::BLOCK_START_TYPE, '', 0));
//
////
////            $parser->parse($stream, function(Token $token) {
////                return $token->test('endmacro');
////            },true);
//            var_dump($parser);exit;
//
//            $node = new TextNode('{% if is_debug() %}vvv{% elseif is_very_verbose() %}vv{% elseif is_verbose() %}v{% else %}q{% endif %}', -1);
//
//
//            $node->setNode('macros', new MacroNode(
//                'verbose_flags',
//                new BodyNode([$node]),
//                -1,
//                'macro'
//            ));
        }

        return $node;
    }

    protected function doLeaveNode(Node $node, Environment $env)
    {

//        if ($this->inmodule ) {
//            echo ++$this->c, "\n";
//            $dumper = new \Twig\Profiler\Dumper\TextDumper();
//            echo $dumper->dump($this->profile);
//            $this->inmodule = false;
//        }

        if ($node instanceof ModuleNode) {

//            $dumper = new \Twig\Profiler\Dumper\TextDumper();
//            $data = implode(
//                "\n",
//                array_map(
//                    function($v) {
//                        return '# ' . $v;
//                    },
//                    explode(
//                        "\n",
//                        $dumper->dump($this->profile)
//                    )
//                )
//            )
//            ;

//            $this->profile->reset();

//            $this->inmodule = false;


//            echo $node->count(), "\n";
//            $text = '# ' . implode('->', $this->stack) . "\n";
//            array_pop($this->stack);
//
//            $tmpl = $env->createTemplate('{% macro vf() %}cccc{% endmacro %}', 'macro_');
//            var_dump($tmpl );exit;

//            var_dump($node);
//            $varName = $this->getVarName();
//            var_dump($node);exit;
//var_dump($node->getNode('display_end'));exit;



//            $node->setNode('display_start', new Node([new TextNode($text, 0),$node->getNode('display_start')]));
//            $node->setNode('display_start', new Node([new TextNode($data, 0),$node->getNode('display_start')]));


//            $node->setNode('display_start', new Node([new DebugNode(), new MacroNode([], ['macros' => $this->macros]), $node->getNode('display_start')]));
//            $node->setNode('display_end', new Node([new LeaveProfileNode($varName), $node->getNode('display_end')]));
        }
//        else if ($node instanceof IncludeNode) {
//            var_dump($node);
//        }

//        if ($node instanceof ModuleNode) {
//            $varName = $this->getVarName();
//            $node->setNode('display_start', new Node([new EnterProfileNode($this->extensionName, Profile::TEMPLATE, $node->getTemplateName(), $varName), $node->getNode('display_start')]));
//            $node->setNode('display_end', new Node([new LeaveProfileNode($varName), $node->getNode('display_end')]));
//        } elseif ($node instanceof BlockNode) {
//            $varName = $this->getVarName();
//            $node->setNode('body', new BodyNode([
//                new EnterProfileNode($this->extensionName, Profile::BLOCK, $node->getAttribute('name'), $varName),
//                $node->getNode('body'),
//                new LeaveProfileNode($varName),
//            ]));
//        } elseif ($node instanceof MacroNode) {
//            $varName = $this->getVarName();
//            $node->setNode('body', new BodyNode([
//                new EnterProfileNode($this->extensionName, Profile::MACRO, $node->getAttribute('name'), $varName),
//                $node->getNode('body'),
//                new LeaveProfileNode($varName),
//            ]));
//        }

        return $node;
    }

    public function getPriority()
    {
        return 0;
    }
}
