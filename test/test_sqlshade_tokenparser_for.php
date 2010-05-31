<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/TokenParser/For.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Parser.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');

$t = new lime_test();

// @setup
$env = new SQLShade_Environment();

// @test
$stream = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'for', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'in', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'items', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::TEXT_TYPE, 'text here', 1),

        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'endfor', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'example.sql'
    );
$token = $stream->next(); // for
$stream->next();

$driveparser = new SQLShade_Parser($env);
$driveparser->setStream($stream);
$tokenparser = new SQLShade_TokenParser_For();
$tokenparser->setParser($driveparser);

$node = $tokenparser->parse($token);
$t->ok($node instanceof SQLShade_Node_For,
       'SQLShade_TokenParser_For generates instance of SQLShade_Node_For');

$t->is($node->getNodeTag(), 'for');
$t->ok($node->getItem() instanceof SQLShade_Node_Expression_AssignName,
       'getItem() returns instance of SQLShade_Node_Expression_AssignName');
$t->ok($node->getIdent() instanceof SQLShade_Node_Expression_Name,
       'getIdent() returns instance of SQLShade_Node_Expression_Name');

$nodes = $node->getChildren();
$t->is(count($nodes), 1);
$t->ok($nodes[0] instanceof SQLShade_Node_Literal);