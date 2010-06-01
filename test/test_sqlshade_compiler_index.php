<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Compiler/Index.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Environment.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Module.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Compound.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Literal.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/CompiledTemplate.php');

function loadsource($source) {
    eval('?>'.$source);
}

$t = new lime_test();
$env = new SQLShade_Environment();
$compiler = new SQLShade_Compiler_Index($env);

// @test
$templateName = 'template_1.sql';
$node = new SQLShade_Node_Module(
    new SQLShade_Node_Compound(
        array(
            new SQLShade_Node_Literal('SELECT * FROM t_table;', 1),
            ), 1),
    $templateName);
$source = $compiler->compile($node);
loadsource($source);

$cls = $env->getTemplateClass($templateName);
$template = new $cls();
$t->is($template->render(array()), 'SELECT * FROM t_table;',
       'CompiledTemplate class has method render()');
$t->is($template->getName(), $templateName,
       'CompiledTemplate class has method getName()');
