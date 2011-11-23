<?php
require_once(dirname(__FILE__).'/../lib/bootstrap.php');
require_once(dirname(__FILE__).'/../../lib/SQLShade/Template.php');

$t = new lime_test();

$plainQuery = <<< SQL
select
    t_store.uid
    , t_store.address1
    , t_store.address2
from
    t_store
where
    true
    and t_store.is_available = /*:is_available*/1
    and t_store.deleted_at is null
group by
    t_store.uid
    , t_store.address1
    , t_store.address2
;
SQL;

$expected = <<< EXPECTED
select
    t_store.uid
    , t_store.address1
    , t_store.address2
from
    t_store
where
    true
    and t_store.is_available = ?
    and t_store.deleted_at is null
group by
    t_store.uid
    , t_store.address1
    , t_store.address2
;
EXPECTED;

$template = new SQLShade_Template($plainQuery, array('strict' => false));
list($query, $bound) = $template->render(array('is_available' => 1));
$t->is($query, $expected);
