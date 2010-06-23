<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Renderer/Dictionary.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Module.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Compound.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Literal.php');

require_once(dirname(__FILE__).'/node_collections.php');

$t = new lime_test();
$env = new SQLShade_Environment();
$renderer = new SQLShade_Renderer_Dictionary($env);

// @test only sql literal
$node = NodeCollections::no_blocks_and_no_vars('SELECT * FROM t_table;');
list($query, $bound) = $renderer->render($node, array());

$t->is($query, 'SELECT * FROM t_table;', 'generates query for prepare');
$t->is($bound, array(), 'bound variables are empty');

// @test a substitute
$node = NodeCollections::one_substitute('uid', '123456');
list($query, $bound) = $renderer->render($node, array('uid' => 3456));
$t->is($query, ':uid', 'scalar makes one placeholder');
$t->is_deeply($bound, array('uid' => 3456));

list($query, $bound) = $renderer->render($node, array('uid' => array(1, 2, 3, 4)));
$t->is($query, '(:uid_1, :uid_2, :uid_3, :uid_4)', 'array makes paren placeholders');
$t->is($bound, array('uid_1' => 1, 'uid_2' => 2, 'uid_3' => 3, 'uid_4' => 4), 'bound 4 variables');

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

// @test if
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
$t->is($query,
       "AND desc LIKE '%' || :keyword_1 || '%' AND desc LIKE '%' || :keyword_2 || '%' AND desc LIKE '%' || :keyword_3 || '%' ",
       'expand 3 times');
$t->is($bound, array('keyword_1' => 'mc', 'keyword_2' => 'mos', 'keyword_3' => "denny's"));

// @test for
$node = NodeCollections::iterate_in_keyword_conditions();
list($query, $bound) = $renderer->render(
    $node, array('keywords' => array(array(1, 2), array(3, 4), array(5, 6))));
$t->is($query, "OR desc IN (:and_kws_1_1, :and_kws_1_2) OR desc IN (:and_kws_2_1, :and_kws_2_2) OR desc IN (:and_kws_3_1, :and_kws_3_2) ",
       '3 loops per list values');
$t->is_deeply($bound, array('and_kws_1_1' => 1, 'and_kws_1_2' => 2,
                     'and_kws_2_1' => 3, 'and_kws_2_2' => 4,
                     'and_kws_3_1' => 5, 'and_kws_3_2' => 6));

// @test
$node = NodeCollections::object_syntax_iteration();
$context = array(
    'iterate_values' => array(
        array('ident' => 1105, 'password' => 'kjim_pass', 'status' => array(1, 2)),
        array('ident' => 3259, 'password' => 'anon_pass', 'status' => array(1, 3)),
        ),
    );
list($query, $bound) = $renderer->render($node, $context);
$t->is($query, " OR (ident = :iteritem.ident_1 AND password = :iteritem.password_1 AND status IN (:iteritem.status_1_1, :iteritem.status_1_2)) OR (ident = :iteritem.ident_2 AND password = :iteritem.password_2 AND status IN (:iteritem.status_2_1, :iteritem.status_2_2))",
       '2 loops');
$t->is_deeply($bound, array('iteritem.ident_1' => 1105, 'iteritem.password_1' => 'kjim_pass',
                            'iteritem.status_1_1' => 1,
                            'iteritem.status_1_2' => 2,

                            'iteritem.ident_2' => 3259, 'iteritem.password_2' => 'anon_pass',
                            'iteritem.status_2_1' => 1,
                            'iteritem.status_2_2' => 3),
              '2 loops bound variables are co');
