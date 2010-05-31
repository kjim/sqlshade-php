<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/SyntaxError.php');

$t = new lime_test();

// @test
$filename = 'template.p';
$lineno = 10;
$error = new SQLShade_SyntaxError('Unknown tag name', $lineno, $filename);
$t->is("Unknown tag name in file '$filename' at line: $lineno", $error->getMessage(), 'format error');

// @test
$lineno = 2;
$error = new SQLShade_SyntaxError('Unknown tag name', $lineno);
$t->is("Unknown tag name at line: $lineno", $error->getMessage(), 'format error');
