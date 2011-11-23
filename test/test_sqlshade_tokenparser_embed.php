<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/TokenParser/Embed.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Parser.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Embed.php');

$t = new lime_test();

// @setup
$env = new SQLShade_Environment();
$driveparser = new SQLShade_Parser($env);
$tokenparser = new SQLShade_TokenParser_Embed();
$tokenparser->setParser($driveparser);

// @test
$stream = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'embed', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::TEXT_TYPE, 'text here', 1),

        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'endembed', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'example.sql'
    );
$token = $stream->next(); // embed
$stream->next();
$driveparser->setStream($stream);

$node = $tokenparser->parse($token);
$t->isa_ok($node, "SQLShade_Node_Embed",
           'SQLShade_TokenParser_Embed generates instance of SQLShade_Node_Embed');

$t->isa_ok($node->getExpr(), "SQLShade_Node_Expression_Name");
$nodes = $node->getChildren();
$t->is(count($nodes), 0);

// @test
$stream = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'embed', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::TEXT_TYPE, 'text here', 1),

        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'endembed', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),

        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'example.sql'
    );
$embedToken = $stream->next();
$stream->next();
$driveparser->setStream($stream);

$node = $tokenparser->parse($embedToken);
$t->isa_ok($node, "SQLShade_Node_Embed",
       'SQLShade_TokenParser_Embed generates instance of SQLShade_Node_Embed');
$t->isa_ok($node->getExpr(), "SQLShade_Node_Expression_Name");
