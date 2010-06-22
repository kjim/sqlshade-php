<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/RenderContext.php');

$t = new lime_test();

// @test
$context = new SQLShade_RenderContext(
    array(
        'keyword' => 'keyword value',
        'password' => 'Hi83i92u',
        ));
$t->is($context->data['keyword'], 'keyword value');
$t->is($context->data['password'], 'Hi83i92u');

// @test
$context = new SQLShade_RenderContext(
    array(
        'environ' => array(
            'distribution' => 'ubuntu 9.10',
            'os' => 'Linux',
            'kernel' => '2.6.31-21-generic',
            ),
        ));
$t->is($context->data['environ.distribution'], 'ubuntu 9.10');
$t->is($context->data['environ.os'], 'Linux');
$t->is($context->data['environ.kernel'], '2.6.31-21-generic');

try {
    $context->data['environ.undefined'];
    $t->fail();
} catch (SQLShade_KeyError $e) {
    $t->pass();
}

// @test
$context = new SQLShade_RenderContext(
    array(
        'top' => array(
            'secound' => array(
                'third' => array(
                    'data' => 'complex structure'
                    ),
                ),
            ),
        ));
$t->is($context->data['top.secound.third.data'], 'complex structure');

// @test
$context = new SQLShade_RenderContext(array());
try {
    $context->data['undefined'];
    $t->fail();
} catch (SQLShade_KeyError $e) {
    $t->pass();
}

$cloneContext = clone($context);
$cloneContext->data->update(array('prop1' => 'value_of_prop1'));
$t->is($cloneContext->data['prop1'], 'value_of_prop1');
try {
    $context->data['prop1'];
    $t->fail();
} catch (SQLShade_KeyError $e) {
    $t->pass();
}
