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

    static public function _embed($expr, $content, $lineno = 1)
    {
        return new SQLShade_Node_Embed($expr, $content, $lineno);
    }

    static public function _embed_with_name($name, $content, $lineno = 1)
    {
        return self::_embed(new SQLShade_Node_Expression_Name($name, $lineno), $content, $lineno);
    }

    static public function _if($expr, $content, $lineno = 1)
    {
        return new SQLShade_Node_If($expr, $content, $lineno);
    }

    static public function _if_with_name($name, $content, $lineno = 1)
    {
        return self::_if(new SQLShade_Node_Expression_Name($name, $lineno), $content, $lineno);
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

    static public function replace_select_from_table($embedid = 'table')
    {
        $node = self::_compound(
            array(
                self::_literal('SELECT * FROM '),
                self::_embed_with_name($embedid, self::_compound(array(self::_literal('t_table')))),
                self::_literal(';'),
                ));
        return self::_module($node);
    }

    static public function enable_or_disable_true_condition($ifid = 'boolean_item')
    {
        $node = self::_compound(
            array(
                self::_literal('SELECT * FROM t_table '),
                self::_if_with_name($ifid, self::_compound(array(self::_literal('WHERE TRUE')))),
                self::_literal(';'),
                ));
        return self::_module($node);
    }
}
