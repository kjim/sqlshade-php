<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Renderer/Index.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Module.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Compound.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Literal.php');

$t = new lime_test();
$env = new SQLShade_Environment();
$renderer = new SQLShade_Renderer_Index($env);

// @test
$templateName = 'test_literal.sql';
$node = new SQLShade_Node_Module(
    new SQLShade_Node_Compound(
        array(
            new SQLShade_Node_Literal('SELECT * FROM t_table;', 1),
            ), 1),
    $templateName);
list($query, $bound) = $renderer->render($node, array());

$t->is($query, 'SELECT * FROM t_table;', 'generates query for prepare');
$t->is($bound, array(), 'bound variables are empty');

// @test
$templateName = 'test_substitute.sql';
$node = new SQLShade_Node_Module(
    new SQLShade_Node_Compound(
        array(
            new SQLShade_Node_Substitute(
                new SQLShade_Node_Expression_Name('uid', 1), '123456', 1),
            ), 1),
    $templateName);
list($query, $bound) = $renderer->render($node, array('uid' => 3456));
$t->is($query, '?', 'scalar makes one placeholder');
$t->is($bound, array(3456));

list($query, $bound) = $renderer->render($node, array('uid' => array(1, 2, 3, 4)));
$t->is($query, '(?, ?, ?, ?)', 'array makes paren placeholders');
$t->is($bound, array(1, 2, 3, 4), 'bound 4 variables');

try {
    $renderer->render($node, array());
    $t->fail();
} catch (SQLShade_RenderError $e) {
    $t->pass('raise error if not pass parameter in context');
}

// @test
$templateName = 'test_embed.sql';
$node = new SQLShade_Node_Module(
    new SQLShade_Node_Compound(
        array(
            new SQLShade_Node_Literal('SELECT * FROM ', 1),
            new SQLShade_Node_Embed(
                new SQLShade_Node_Expression_Name('table', 1),
                new SQLShade_Node_Compound(
                    array(
                        new SQLShade_Node_Literal('t_table', 1),
                        ),
                    1),
                1),
            new SQLShade_Node_Literal(';', 1),
            ),
        1),
    $templateName);
list($query, $bound) = $renderer->render($node, array('table' => 't_table_extension'));
$t->is($query, 'SELECT * FROM t_table_extension;', 'embed t_table_extension');

list($query, $bound) = $renderer->render($node, array('table' => 't_table_test'));
$t->is($query, 'SELECT * FROM t_table_test;', 'embed t_table_test');
