<?php
require_once(dirname(__FILE__).'/../../lib/SQLShade/Node/Module.php');
require_once(dirname(__FILE__).'/../../lib/SQLShade/Node/Compound.php');
require_once(dirname(__FILE__).'/../../lib/SQLShade/Node/Literal.php');
require_once(dirname(__FILE__).'/../../lib/SQLShade/Node/Substitute.php');

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

    static public function _for($assignexpr, $listexpr, $content, $lineno = 1)
    {
        return new SQLShade_Node_For($assignexpr, $listexpr, $content, $lineno);
    }

    static public function _for_with_name($assignname, $listname, $content, $lineno = 1)
    {
        return self::_for(
            new SQLShade_Node_Expression_AssignName($assignname, 1),
            new SQLShade_Node_Expression_Name($listname, 1),
            $content
            );
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

    static public function iterate_keyword_conditions($asid = 'keyword', $listid = 'keywords')
    {
        $forcontent = self::_compound(
            array(
                self::_literal("AND desc LIKE '%' || "),
                self::_substitute_with_name($asid, '123456'),
                self::_literal(" || '%' "),
                ));
        $node = self::_for_with_name($asid, $listid, $forcontent);
        return self::_module(self::_compound(array($node)));
    }

    static public function iterate_in_keyword_conditions($asid = 'and_kws', $listid = 'keywords')
    {
        $forcontent = self::_compound(
            array(
                self::_literal("OR desc IN "),
                self::_substitute_with_name($asid, '123456'),
                self::_literal(" "),
                ));
        $node = self::_for_with_name($asid, $listid, $forcontent);
        return self::_module(self::_compound(array($node)));
    }

    static public function object_syntax_iteration($asid = 'iteritem', $listid = 'iterate_values')
    {
        $forcontent = self::_compound(
            array(
                self::_literal(' OR (ident = '),
                self::_substitute_with_name("$asid.ident", 9999),
                self::_literal(' AND password = '),
                self::_substitute_with_name("$asid.password", 'test_pass'),
                self::_literal(' AND status IN '),
                self::_substitute_with_name("$asid.status", '(1, 2, 3)'),
                self::_literal(')'),
                ));
        $node = self::_for_with_name($asid, $listid, $forcontent);
        return self::_module(self::_compound(array($node)));
    }
}
