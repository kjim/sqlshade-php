<?php
require_once(dirname(__FILE__).'/../bootstrap.php');
require_once(dirname(__FILE__).'/../../lib/SQLShade/Template.php');

$t = new lime_test();

$plainQuery = <<< SQL
select
    t.uid
from
    t
where
    true
    and t.deleted_at = /*:deleted_at*/null
SQL;

$expected = <<< EXPECTED
select
    t.uid
from
    t
where
    true
    and t.deleted_at = ?
EXPECTED;

$template = new SQLShade_Template($plainQuery, array('strict' => true));
list($query, $bound) = $template->render(array('deleted_at' => null));
$t->is_deeply($query, $expected);
$t->is_deeply($bound, array(null));

$template = new SQLShade_Template($plainQuery, array('strict' => false));
list($query, $bound) = $template->render(array('deleted_at' => null));
$t->is_deeply($query, $expected);
$t->is_deeply($bound, array(null));
