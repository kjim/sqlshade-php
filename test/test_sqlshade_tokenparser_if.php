<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/TokenParser/If.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Parser.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/If.php');

$t = new lime_test();

// @setup
$env = new SQLShade_Environment();
$driveparser = new SQLShade_Parser($env);
$tokenparser = new SQLShade_TokenParser_If();
$tokenparser->setParser($driveparser);

// @test
$stream = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'if', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::TEXT_TYPE, 'text here', 1),

        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'endif', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'example.sql'
    );
$token = $stream->next(); // if
$stream->next();
$driveparser->setStream($stream);

$node = $tokenparser->parse($token);
$t->isa_ok($node, "SQLShade_Node_If",
       'SQLShade_TokenParser_If generates instance of SQLShade_Node_If');

$t->isa_ok($node->getExpr(), "SQLShade_Node_Expression_Name");
$nodes = $node->getChildren();
$t->is(count($nodes), 1);
$t->isa_ok($nodes[0], "SQLShade_Node_Literal");

// @test deparse
$tokens = $tokenparser->deparse($node);
is_tokens_order($t, $tokens,
                array(array(SQLShade_Token::BLOCK_START_TYPE, ''),
                      array(SQLShade_Token::NAME_TYPE, 'if'),
                      array(SQLShade_Token::NAME_TYPE, 'item'),
                      array(SQLShade_Token::BLOCK_END_TYPE, ''),
                      array(SQLShade_Token::TEXT_TYPE, 'text here'),
                      array(SQLShade_Token::BLOCK_START_TYPE, ''),
                      array(SQLShade_Token::NAME_TYPE, 'endif'),
                      array(SQLShade_Token::BLOCK_END_TYPE, ''),
                    ));

// @test
$stream = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'if', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'not', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::TEXT_TYPE, 'text here', 1),

        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'endif', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'example.sql'
    );
$ifToken = $stream->next();
$stream->next();
$driveparser->setStream($stream);

$node = $tokenparser->parse($ifToken);
$t->isa_ok($node, "SQLShade_Node_If",
       'SQLShade_TokenParser_If generates instance of SQLShade_Node_If');
$t->isa_ok($node->getExpr(), "SQLShade_Node_Expression_Unary_Not");
$t->isa_ok($node->getExpr()->getNode(), "SQLShade_Node_Expression_Name");
$t->is($node->getExpr()->getNode()->getName(), "item");

// @test deparse
$tokens = $tokenparser->deparse($node);
is_tokens_order($t, $tokens,
                array(array(SQLShade_Token::BLOCK_START_TYPE, ''),
                      array(SQLShade_Token::NAME_TYPE, 'if'),
                      array(SQLShade_Token::NAME_TYPE, 'not'),
                      array(SQLShade_Token::NAME_TYPE, 'item'),
                      array(SQLShade_Token::BLOCK_END_TYPE, ''),
                      array(SQLShade_Token::TEXT_TYPE, 'text here'),
                      array(SQLShade_Token::BLOCK_START_TYPE, ''),
                      array(SQLShade_Token::NAME_TYPE, 'endif'),
                      array(SQLShade_Token::BLOCK_END_TYPE, ''),
                    ));
