<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/node_collections.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Renderer/Array.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Module.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Compound.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Literal.php');

$t = new lime_test();
$env = new SQLShade_Environment();
$renderer = new SQLShade_Renderer_Array($env);

// @test only sql literal
$node = NodeCollections::no_blocks_and_no_vars('SELECT * FROM t_table;');
list($query, $bound) = $renderer->render($node, array());

$t->is($query, 'SELECT * FROM t_table;', 'generates query for prepare');
$t->is($bound, array(), 'bound variables are empty');

// @test substitute
$node = NodeCollections::one_substitute('uid', 3456);
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

// @test embed
$node = NodeCollections::replace_select_from_table();
list($query, $bound) = $renderer->render($node, array('table' => 't_table_extension'));
$t->is($query, 'SELECT * FROM t_table_extension;', 'embed t_table_extension');

list($query, $bound) = $renderer->render($node, array('table' => 't_table_test'));
$t->is($query, 'SELECT * FROM t_table_test;', 'embed t_table_test');

// @test
$node = NodeCollections::enable_or_disable_true_condition();

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

// @test for
$node = NodeCollections::iterate_keyword_conditions();
list($query, $bound) = $renderer->render($node, array('keywords' => array('mc', 'mos', "denny's")));
$t->is($query, str_repeat("AND desc LIKE '%' || ? || '%' ", 3), '3 loops per scalar values');
$t->is($bound, array('mc', 'mos', "denny's"));

// @test
$node = NodeCollections::iterate_in_keyword_conditions();
list($query, $bound) = $renderer->render(
    $node, array('keywords' => array(array(1, 2), array(3, 4), array(5, 6))));
$t->is($query, str_repeat("OR desc IN (?, ?) ", 3), '3 loops per list values');
$t->is($bound, array(1, 2, 3, 4, 5, 6));

// @test
$node = NodeCollections::object_syntax_iteration();
$context = array(
    'iterate_values' => array(
        array('ident' => 1105, 'password' => 'kjim_pass', 'status' => array(1, 2)),
        array('ident' => 3259, 'password' => 'anon_pass', 'status' => array(1, 3)),
        ),
    );
list($query, $bound) = $renderer->render($node, $context);
$t->is($query, str_repeat(" OR (ident = ? AND password = ? AND status IN (?, ?))", 2), '2 loops');
$t->is($bound, array(1105, 'kjim_pass', 1, 2, 3259, 'anon_pass', 1, 3), '2 loops bound variables are co');
