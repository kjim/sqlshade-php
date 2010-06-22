<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Printer/Index.php');

$t = new lime_test();

// @test
$printer = new SQLShade_Printer_Index();
$printer->write("SELECT * FROM t_table WHERE t_table.status = ?");
$printer->bind(1);
list($query, $bound) = $printer->freeze();
$t->is($query, "SELECT * FROM t_table WHERE t_table.status = ?");
$t->is($bound, array(1));
