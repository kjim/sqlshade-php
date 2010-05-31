<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/TokenParser/If.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Parser.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/If.php');

$t = new lime_test();

// @setup
$env = new SQLShade_Environment();

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

$driveparser = new SQLShade_Parser($env);
$driveparser->setStream($stream);
$tokenparser = new SQLShade_TokenParser_If();
$tokenparser->setParser($driveparser);

$node = $tokenparser->parse($token);
$t->ok($node instanceof SQLShade_Node_If,
       'SQLShade_TokenParser_If generates instance of SQLShade_Node_If');

$t->ok($node->getIdent() instanceof SQLShade_Node_Expression_Name);
$nodes = $node->getChildren();
$t->is(count($nodes), 1);
$t->ok($nodes[0] instanceof SQLShade_Node_Literal);
