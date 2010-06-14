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

// @test
$templateName = 'test_if.sql';
$node = new SQLShade_Node_Module(
    new SQLShade_Node_Compound(
        array(
            new SQLShade_Node_Literal('SELECT * FROM t_table ', 1),
            new SQLShade_Node_If(
                new SQLShade_Node_Expression_Name('boolean_item', 1),
                new SQLShade_Node_Compound(
                    array(
                        new SQLShade_Node_Literal('WHERE TRUE', 1),
                        ),
                    1),
                1),
            new SQLShade_Node_Literal(';', 1),
            ),
        1),
    $templateName);
// boolean
list($query, $_) = $renderer->render($node, array('boolean_item' => true));
$t->like($query, '/WHERE TRUE/', 'true is enable if-block');

list($query, $_) = $renderer->render($node, array('boolean_item' => false));
$t->unlike($query, '/WHERE TRUE/', 'false is disable if-block');

// numeric
list($query, $_) = $renderer->render($node, array('boolean_item' => 1));
$t->like($query, '/WHERE TRUE/', '1 is enable if-block');

list($query, $_) = $renderer->render($node, array('boolean_item' => -1));
$t->like($query, '/WHERE TRUE/', '-1 is enable if-block');

list($query, $_) = $renderer->render($node, array('boolean_item' => 0));
$t->unlike($query, '/WHERE TRUE/', '0 is disable if-block');

// string
list($query, $_) = $renderer->render($node, array('boolean_item' => 'some string'));
$t->like($query, '/WHERE TRUE/', '"some string" is enable if-block');

list($query, $_) = $renderer->render($node, array('boolean_item' => ''));
$t->unlike($query, '/WHERE TRUE/', '"" is disable if-block');

// array
list($query, $_) = $renderer->render($node, array('boolean_item' => array(1, 2, 3)));
$t->like($query, '/WHERE TRUE/', 'array(1, 2, 3) is enable if-block');

list($query, $_) = $renderer->render($node, array('boolean_item' => array()));
$t->unlike($query, '/WHERE TRUE/', 'array() is disable if-block');

// @test
$templateName = 'test_for_scalar.sql';
$node = new SQLShade_Node_Module(
    new SQLShade_Node_Compound(
        array(
            new SQLShade_Node_For(
                new SQLShade_Node_Expression_AssignName('keyword', 1),
                new SQLShade_Node_Expression_Name('keywords', 1),
                new SQLShade_Node_Compound(
                    array(
                        new SQLShade_Node_Literal("AND desc LIKE '%' || ", 1),
                        new SQLShade_Node_Substitute(
                            new SQLShade_Node_Expression_Name('keyword', 1),
                            '123456', 1),
                        new SQLShade_Node_Literal(" || '%' ", 1),
                        ),
                    1),
                1),
            ),
        1),
    $templateName);
list($query, $bound) = $renderer->render($node, array('keywords' => array('mc', 'mos', "denny's")));
$t->is($query, str_repeat("AND desc LIKE '%' || ? || '%' ", 3), '3 loops per scalar values');
$t->is($bound, array('mc', 'mos', "denny's"));

// @test
$templateName = 'test_for_list.sql';
$node = new SQLShade_Node_Module(
    new SQLShade_Node_Compound(
        array(
            new SQLShade_Node_For(
                new SQLShade_Node_Expression_AssignName('and_kws', 1),
                new SQLShade_Node_Expression_Name('keywords', 1),
                new SQLShade_Node_Compound(
                    array(
                        new SQLShade_Node_Literal("OR desc IN '%' || ", 1),
                        new SQLShade_Node_Substitute(
                            new SQLShade_Node_Expression_Name('and_kws', 1),
                            '123456', 1),
                        new SQLShade_Node_Literal(" || '%' ", 1),
                        ),
                    1),
                1),
            ),
        1),
    $templateName);
list($query, $bound) = $renderer->render(
    $node, array('keywords' => array(array(1, 2), array(3, 4), array(5, 6))));
$t->is($query, str_repeat("OR desc IN '%' || (?, ?) || '%' ", 3), '3 loops per list values');
$t->is($bound, array(1, 2, 3, 4, 5, 6));

// @test
$templateName = 'test_for_named_iteration.sql';
$node = new SQLShade_Node_Module(
    new SQLShade_Node_Compound(
        array(
            new SQLShade_Node_For(
                new SQLShade_Node_Expression_AssignName('iteritem', 1),
                new SQLShade_Node_Expression_Name('iterate_values', 1),
                new SQLShade_Node_Compound(
                    array(
                        new SQLShade_Node_Literal(' OR (ident = ', 1),
                        new SQLShade_Node_Substitute(
                            new SQLShade_Node_Expression_Name('iteritem.ident', 1), 9999, 1),
                        new SQLShade_Node_Literal(' AND password = ', 1),
                        new SQLShade_Node_Substitute(
                            new SQLShade_Node_Expression_Name('iteritem.password', 1), 'test_pass', 1),
                        new SQLShade_Node_Literal(' AND status IN ', 1),
                        new SQLShade_Node_Substitute(
                            new SQLShade_Node_Expression_Name('iteritem.status', 1), '(1, 2, 3)', 1),
                        new SQLShade_Node_Literal(')', 1),
                        ),
                    1),
                1),
            ),
        1),
    $templateName);
$context = array(
    'iterate_values' => array(
        array('ident' => 1105, 'password' => 'kjim_pass', 'status' => array(1, 2)),
        array('ident' => 3259, 'password' => 'anon_pass', 'status' => array(1, 3)),
        ),
    );
list($query, $bound) = $renderer->render($node, $context);
$t->is($query, str_repeat(" OR (ident = ? AND password = ? AND status IN (?, ?))", 2), '2 loops');
$t->is($bound, array(1105, 'kjim_pass', 1, 2, 3259, 'anon_pass', 1, 3), '2 loops bound variables are co');
