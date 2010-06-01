<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Parser.php');

$t = new lime_test();

// @setup
$env = new SQLShade_Environment();
$parser = new SQLShade_Parser($env);

// @test
$stream1 = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE, 'text here', 1),
        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'example.sql'
    );
$node = $parser->parse($stream1);
$t->ok($node instanceof SQLShade_Node_Module,
       'return value is instance of SQLShade_Node_Module');

$t->is($node->getFilename(), 'example.sql', 'filename is example.sql');
$t->ok($node->getBody() instanceof SQLShade_Node_Compound,
       'getBody() returns instance of SQLShade_Node_Compound');

$nodes = $node->getBody()->getChildren();
$t->is(count($nodes), 1);
$t->ok($nodes[0] instanceof SQLShade_Node_Literal,
       'nodes[0] is instance of SQLShade_Node_Literal');

// @test
$stream2 = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           'SELECT * FROM t_table WHERE TRUE ', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'if', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           'AND t_table.uid = 1', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'endif', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 1),
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           ';', 1),
        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'example.sql'
    );
$node = $parser->parse($stream2);
$nodes = $node->getBody()->getChildren();
$t->is(count($nodes), 3, 'count(nodes) is 3');

// test 1st node
$t->ok($nodes[0] instanceof SQLShade_Node_Literal,
       '$nodes[0] is instanceof SQLShade_Node_Literal');
$t->is($nodes[0]->getLiteral(), 'SELECT * FROM t_table WHERE TRUE ');

// test 2nd node
$t->ok($nodes[1] instanceof SQLShade_Node_If,
       '$nodes[1] is instanceof SQLShade_Node_If');
$ifnodes = $nodes[1]->getChildren();
$t->is(count($ifnodes), 1);
$t->ok($ifnodes[0] instanceof SQLShade_Node_Literal);
$t->is($ifnodes[0]->getLiteral(), 'AND t_table.uid = 1');

// test 3rd node
$t->ok($nodes[2] instanceof SQLShade_Node_Literal,
       '$nodes[2] is instanceof SQLShade_Node_Literal');
$t->is($nodes[2]->getLiteral(), ';');
