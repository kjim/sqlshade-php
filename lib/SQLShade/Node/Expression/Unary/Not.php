<?php
require_once(dirname(__FILE__).'/../../Expression.php');

class SQLShade_Node_Expression_Unary_Not extends SQLShade_Node_Expression {

    protected $node;

    public function __construct($node, $lineno, $token) {
        parent::__construct($lineno, $token);

        $this->node  = $node;
    }

    public function getNode() {
        return $this->node;
    }

}
