<?php
require_once(dirname(__FILE__).'/lib/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Printer/Dictionary.php');

$t = new lime_test();

// @test
$printer = new SQLShade_Printer_Dictionary();
$printer->write("SELECT * FROM t_table WHERE t_table.status = :status_open");
$printer->bind('status_open', 1);
list($query, $bound) = $printer->freeze();
$t->is($query, "SELECT * FROM t_table WHERE t_table.status = :status_open");
$t->is($bound, array('status_open' => 1));
