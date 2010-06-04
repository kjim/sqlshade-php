<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Template.php');

$t = new lime_test();

// @test
$plainQuery = "SELECT * FROM t_member WHERE name = /*:nickname*/'kjim'";
$parameters = array('nickname' => 'keiji');
$template = new SQLShade_Template($plainQuery);
list($query, $bound) = $template->render($parameters);
$t->is($query, "SELECT * FROM t_member WHERE name = ?", 'generates simple query');
$t->is($bound, array('keiji'), '1 bound variable');

// @test
$plainQuery = "SELECT * FROM t_member
    WHERE TRUE
        AND t_member.age = /*:age*/1000
        AND t_member.nickname = /*:nickname*/'my nickname is holder'
        AND t_member.updated_at = /*:updated_at*/CURRENT_TIMESTAMP
        AND t_member.created_at <= /*:created_at*/now()
    ;
";
list($createdAt, $updatedAt) = array(new DateTime('2010-06-03'), new DateTime('2010-06-03'));
$parameters = array(
    'age' => 25,
    'nickname' => 'kjim',
    'created_at' => $createdAt,
    'updated_at' => $updatedAt,
    );

$template = new SQLShade_Template($plainQuery);
list($query, $bound) = $template->render($parameters);
$t->is($query, "SELECT * FROM t_member
    WHERE TRUE
        AND t_member.age = ?
        AND t_member.nickname = ?
        AND t_member.updated_at = ?
        AND t_member.created_at <= ?
    ;
");
$t->is($bound, array(25, 'kjim', $createdAt, $updatedAt));

// @test
$plainQuery = "SELECT * FROM t_member
    WHERE TRUE
        AND t_member.member_id IN /*:member_id*/(100, 200)
        AND t_member.nickname LIKE /*:nickname*/'%kjim%'
        AND t_member.sex IN /*:sex*/('male', 'female')
";
$parameters = array(
    'member_id' => array(3845, 295, 1, 637, 221, 357),
    'nickname' => '%keiji%',
    'sex' => array('male', 'female', 'other'),
    );

$template = new SQLShade_Template($plainQuery);
list($query, $bound)= $template->render($parameters);
$t->is($query, "SELECT * FROM t_member
    WHERE TRUE
        AND t_member.member_id IN (?, ?, ?, ?, ?, ?)
        AND t_member.nickname LIKE ?
        AND t_member.sex IN (?, ?, ?)
");
$t->is($bound, array(3845, 295, 1, 637, 221, 357, '%keiji%', 'male', 'female', 'other'));

// @test
$plainQuery = "SELECT *
    FROM
        t_member
        INNER JOIN t_member_activation
          ON (t_member_activation.member_id = t_member.member_id)
    WHERE TRUE
        AND t_member.satus = /*:status_activated*/0
        AND t_member_activation.status = /*:status_activated*/0
    ;
";
$parameters = array('status_activated' => 1);

$template = new SQLShade_Template($plainQuery);
list($query, $bound) = $template->render($parameters);
$t->is($query, "SELECT *
    FROM
        t_member
        INNER JOIN t_member_activation
          ON (t_member_activation.member_id = t_member.member_id)
    WHERE TRUE
        AND t_member.satus = ?
        AND t_member_activation.status = ?
    ;
");
$t->is($bound, array(1, 1));

// @test
$plainQuery = "SELECT * FROM t_member
    WHERE TRUE
        AND t_member.member_id IN /*:member_ids*/(100, 200, 300, 400)
        AND t_member.nickname = /*:nickname*/'kjim'
";
$template = new SQLShade_Template($plainQuery);
try {
    $template->render();
    $t->fail();
} catch (SQLShade_RenderError $e) {
    $t->pass('raise if no variables feeded');
}
try {
    $template->render(array('nickname' => 'keiji'));
    $t->fail();
} catch (SQLShade_RenderError $e) {
    $t->pass();
}

// @test
$plainQuery = "SELECT * FROM t_member
    WHERE TRUE
        AND t_member.member_id IN /*:member_ids*/(100, 200, 300, 400)
";
$template = new SQLShade_Template($plainQuery);
try {
    $template->render(array('member_ids' => array()));
    $t->fail();
} catch (SQLShade_RenderError $e) {
    $t->pass('empty array is invalid variable');
}

// @test
$plainQuery = "SELECT * FROM /*#embed table_name*/t_aggregation_AA/*#endembed*/";
$parameters = array('table_name' => 't_aggregation_BB');

$template = new SQLShade_Template($plainQuery);
list($query, $_) = $template->render($parameters);
$t->is($query, "SELECT * FROM t_aggregation_BB", "embed basic usage");

try {
    $template->render();
    $t->fail();
} catch (SQLShade_RenderError $e) {
    $t->pass("no variable feeded");
}

// @test
$plainQuery = "SELECT * FROM t_member
    WHERE TRUE
        AND t_member.member_id IN /*:member_ids*/(1, 2, 3, 4, 5)
        /*#embed condition_on_runtime*/
        AND (t_member.nickname LIKE '%kjim%' or t_member.email LIKE '%linux%')
        /*#endembed*/
    ;";
$parameters = array(
    'member_ids' => array(23, 535, 2),
    'condition_on_runtime' => "AND t_member.nickname ILIKE 'linus'",
    );

$template = new SQLShade_Template($plainQuery);
list($query, $bound) = $template->render($parameters);
$t->is($query, "SELECT * FROM t_member
    WHERE TRUE
        AND t_member.member_id IN (?, ?, ?)
        AND t_member.nickname ILIKE 'linus'
    ;", "embed works are SQL injection");
$t->is($bound, $parameters['member_ids']);
