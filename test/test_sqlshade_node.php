<?php
require_once(dirname(__FILE__).'/bootstrap.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Compound.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/If.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Literal.php');
require_once(dirname(__FILE__).'/../lib/SQLShade/Node/Substitute.php');

$t = new lime_test();

class Example extends SQLShade_Node {

    public function getVisitName() {
        return parent::getVisitName();
    }

}

class Walker {

    public $logs;

    public function __construct() {
        $this->logs = array();
    }

    public function walk($node) {
        $node->acceptVisitor($this);
    }

    public function visitExample($node, $opts) {
        $this->log('example');
    }

    public function visitIf($node, $opts) {
        $this->log('if');

        foreach ($node->getChildren() as $n) {
            $n->acceptVisitor($this);
        }
    }

    public function visitLiteral($node, $opts) {
        $this->log('literal');
    }

    public function visitSubstitute($node, $opts) {
        $this->log('substitute');
    }

    protected function log($nodetag) {
        $this->logs[] = $nodetag;
    }

}

// @test
$node = new Example(1, null);
$t->is($node->getVisitName(), 'visitExample');

// @test
$walker = new Walker();
$walker->walk(new Example(1, null));
$t->is($walker->logs, array('example'));

// @test
$compound = new SQLShade_Node_Compound(
    array(
        new SQLShade_Node_Literal('WHERE TRUE ', 1),
        new SQLShade_Node_If(
            'uid',
            new SQLShade_Node_Compound(
                array(
                    new SQLShade_Node_Literal('AND uid = ', 1),
                    new SQLShade_Node_Substitute('uid', '123456', 1),
                    )),
            1),
        ));

$walker = new Walker();
$walker->walk($compound);
$t->is(count($walker->logs), 4);
$t->is($walker->logs, array('literal', 'if', 'literal', 'substitute'));
