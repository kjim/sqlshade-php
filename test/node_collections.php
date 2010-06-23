<?php
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Module.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Compound.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Literal.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Substitute.php');

class NodeCollections
{
    static public function _literal($literal, $lineno = 1)
    {
        return new SQLShade_Node_Literal($literal, $lineno);
    }

    static public function _substitute($expr, $faketext = '', $lineno = 1)
    {
        return new SQLShade_Node_Substitute($expr, $faketext, $lineno);
    }

    static public function _substitute_with_name($name, $faketext = '', $lineno = 1)
    {
        $expr = new SQLShade_Node_Expression_Name($name, $lineno);
        return self::_substitute($expr, $faketext, $lineno);
    }

    static public function _compound($nodes, $lineno = 1)
    {
        return new SQLShade_Node_Compound($nodes, 1);
    }

    static public function _module($node, $templateName = '')
    {
        return new SQLShade_Node_Module($node, $templateName);
    }

    static public function no_blocks_and_no_vars($literal)
    {
        $node = self::_compound(array(self::_literal($literal)));
        return self::_module($node);
    }

    static public function one_substitute($name, $faketext)
    {
        $node = self::_compound(array(self::_substitute_with_name($name)));
        return self::_module($node);
    }
}
