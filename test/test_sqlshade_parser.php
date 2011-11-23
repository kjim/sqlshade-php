<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
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
$t->isa_ok($node, 'SQLShade_Node_Module',
       'return value is instance of SQLShade_Node_Module');

$t->is($node->getFilename(), 'example.sql', 'filename is example.sql');
$t->isa_ok($node->getBody(), 'SQLShade_Node_Compound',
       'getBody() returns instance of SQLShade_Node_Compound');

$nodes = $node->getBody()->getChildren();
$t->is(count($nodes), 1);
$t->isa_ok($nodes[0], 'SQLShade_Node_Literal',
       'nodes[0] is instance of SQLShade_Node_Literal');

// @test
$stream2 = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           'SELECT * FROM t_table WHERE TRUE ', 1),
        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 2),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'if', 2),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'item', 2),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 2),
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           'AND t_table.uid = ', 2),
        new SQLShade_Token(SQLShade_Token::VAR_START_TYPE, '', 2),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'uid', 2),
        new SQLShade_Token(SQLShade_Token::VAR_END_TYPE, '', 2),
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           '832958', 2),
        new SQLShade_Token(SQLShade_Token::BLOCK_START_TYPE, '', 3),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'endif', 3),
        new SQLShade_Token(SQLShade_Token::BLOCK_END_TYPE, '', 3),
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           ';', 4),
        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'example.sql'
    );
$node = $parser->parse($stream2);
$nodes = $node->getBody()->getChildren();
$t->is(count($nodes), 3, 'count(nodes) is 3');

// test 1st node
$t->isa_ok($nodes[0], 'SQLShade_Node_Literal',
       '$nodes[0] is instanceof SQLShade_Node_Literal');
$t->is($nodes[0]->getLiteral(), 'SELECT * FROM t_table WHERE TRUE ');

// test 2nd node
$t->isa_ok($nodes[1], 'SQLShade_Node_If',
       '$nodes[1] is instanceof SQLShade_Node_If');
$ifnodes = $nodes[1]->getChildren();
$t->is(count($ifnodes), 3);
$t->isa_ok($ifnodes[0], 'SQLShade_Node_Literal');
$t->is($ifnodes[0]->getLiteral(), 'AND t_table.uid = ');
$t->isa_ok($ifnodes[1], 'SQLShade_Node_Substitute');
$t->is($ifnodes[1]->getExpr()->getName(), 'uid');
$t->is($ifnodes[1]->getFaketext(), '832958');

// test 4th node
$t->isa_ok($nodes[2], 'SQLShade_Node_Literal',
       '$nodes[2] is instanceof SQLShade_Node_Literal');
$t->is($nodes[2]->getLiteral(), ';');

// @test using japanese name
$stream3 = new SQLShade_TokenStream(
    array(
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           'SELECT column_a as カラムＡ /* 日本語別名 */ FROM t_table as テーブル WHERE TRUE ', 1),
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           "AND テーブル.fulltext like '%' || ", 2),
        new SQLShade_Token(SQLShade_Token::VAR_START_TYPE, '', 2),
        new SQLShade_Token(SQLShade_Token::NAME_TYPE, 'fulltext', 2),
        new SQLShade_Token(SQLShade_Token::VAR_END_TYPE, '', 2),
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           "'サンプルテキスト' || '%'", 2),
        new SQLShade_Token(SQLShade_Token::TEXT_TYPE,
                           ';', 4),
        new SQLShade_Token(SQLShade_Token::EOF_TYPE, '', 1),
        ),
    'using_japanese_name.sql'
    );
$node = $parser->parse($stream3);
$nodes = $node->getBody()->getChildren();
$t->is(count($nodes), 5, 'count(nodes) is 3');

//
$t->is($nodes[0]->getLiteral(),
       "SELECT column_a as カラムＡ /* 日本語別名 */ FROM t_table as テーブル WHERE TRUE ");
$t->is($nodes[1]->getLiteral(), "AND テーブル.fulltext like '%' || ");
$t->isa_ok($nodes[2], "SQLShade_Node_Substitute");
$t->is($nodes[2]->getExpr()->getName(), "fulltext");
$t->is($nodes[3]->getLiteral(), " || '%'");
$t->is($nodes[4]->getLiteral(), ";");
