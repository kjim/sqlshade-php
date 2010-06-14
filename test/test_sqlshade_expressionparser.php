<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/ExpressionParser.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Parser.php');

require_once(dirname(__FILE__).'/../lib/SQLShade/TokenStream.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Token.php');

$t = new lime_test();

// @setup
$env = new SQLShade_Environment();
$driveparser = new SQLShade_Parser($env);
$exprparser = new SQLShade_ExpressionParser($driveparser);

$testname = 'test.sql';

// @test parse
$stream = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::VAR_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::VAR_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    $testname
    );
$stream->next();

$driveparser->setStream($stream);
$node = $exprparser->parseExpression();
$t->isa_ok($node, 'SQLShade_Node_Expression_Name', 'instance of Expression_Name');
$t->is($node->getName(), 'item', 'getName() returns "item"');

// @test parse
$stream = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::VAR_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::OPERATOR_TYPE, '.', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'name', 1),
        new SQLShade_Token(SQLShade_Token::VAR_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    $testname
    );
$stream->next();

$driveparser->setStream($stream);
$node = $exprparser->parseExpression();
$t->isa_ok($node, 'SQLShade_Node_Expression_AttrName', 'instance of Expression_AttrName');

$sourceNode = $node->getNode();
$t->isa_ok($sourceNode, 'SQLShade_Node_Expression_Name', 'source node is instance of Expression_Name');
$t->is($sourceNode->getName(), 'item');

$attrNode = $node->getAttr();
$t->isa_ok($attrNode, 'SQLShade_Node_Expression_Constant', 'attribute node is instance of Constant');
$t->is($attrNode->getValue(), 'name');

// @test
$stream = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::VAR_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::OPERATOR_TYPE, '.', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'name', 1),
        new SQLShade_Token(SQLShade_Token::OPERATOR_TYPE, '.', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'first', 1),
        new SQLShade_Token(SQLShade_Token::VAR_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    $testname
    );
$stream->next();

$driveparser->setStream($stream);
$node1st = $exprparser->parseExpression();
$t->isa_ok($node1st, 'SQLShade_Node_Expression_AttrName', '[1st] instance of Expression_AttrName');

$node1stAttr = $node1st->getAttr();
$t->isa_ok($node1stAttr, 'SQLShade_Node_Expression_Constant', '[1st] attribute is instance of Constant');
$t->is($node1stAttr->getValue(), 'first', '[1st] attribute value is `first`');

$node2th = $node1st->getNode();
$t->isa_ok($node2th, 'SQLShade_Node_Expression_AttrName', '[2nd] source node is instance of Expression_AttrName');

$node2thAttr = $node2th->getAttr();
$t->isa_ok($node2thAttr, 'SQLShade_Node_Expression_Constant', '[2nd] attribute is instance of Constant');
$t->is($node2thAttr->getValue(), 'name', '[2nd] attribute value is `name`');

$node3rd = $node2th->getNode();
$t->isa_ok($node3rd, 'SQLShade_Node_Expression_Name', '[3rd] source node is instance of Expression_Name');
$t->is($node3rd->getName(), 'item', '[3nd] name value is `item`');

function stringify_expression_varname($node) {
    $nodetype = get_class($node);
    if ($nodetype === 'SQLShade_Node_Expression_Name') {
        return $node->getName();
    }
    elseif ($nodetype === 'SQLShade_Node_Expression_Constant') {
        return $node->getValue();
    }
    elseif ($nodetype === 'SQLShade_Node_Expression_AttrName') {
        $left = stringify_expression_varname($node->getNode());
        $right = stringify_expression_varname($node->getAttr());
        return $left . '.' . $right;
    }

    throw new LogicException("Unexpected node type: " . get_class($node));
}

$t->is(stringify_expression_varname($node1st), 'item.name.first', 'stringify');

// @test
$stream = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::VAR_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::OPERATOR_TYPE, '[', 1),
        new SQLShade_Token(SQLShade_Token::STRING_TYPE, 'name', 1),
        new SQLShade_Token(SQLShade_Token::OPERATOR_TYPE, ']', 1),
        new SQLShade_Token(SQLShade_Token::OPERATOR_TYPE, '[', 1),
        new SQLShade_Token(SQLShade_Token::STRING_TYPE, 'first', 1),
        new SQLShade_Token(SQLShade_Token::OPERATOR_TYPE, ']', 1),
        new SQLShade_Token(SQLShade_Token::VAR_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    $testname
    );
$stream->next();

$driveparser->setStream($stream);
$node1st2 = $exprparser->parseExpression();
$t->isa_ok($node1st, 'SQLShade_Node_Expression_AttrName', '[1st] instance of Expression_AttrName');
$t->is_deeply(stringify_expression_varname($node1st2), stringify_expression_varname($node1st));

// @test unary
$stream = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'if', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'not', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),
        ),
    $testname
    );
$stream->next(); // skip block start
$stream->next(); // skip if
$driveparser->setStream($stream);
$node = $exprparser->parseExpression();
$t->isa_ok($node, 'SQLShade_Node_Expression_Unary_Not');

$innerNode = $node->getNode();
$t->isa_ok($innerNode, 'SQLShade_Node_Expression_Name');
$t->is($innerNode->getName(), 'item');
