<?php
require_once(dirname(__FILE__).'/../Node.php');

class SQLShade_Node_If extends SQLShade_Node {

    protected $expr;
    protected $body;

    public function __construct($expr, /*Node_Compound*/$body, $lineno, $token) {
        parent::__construct($lineno, $token);

        $this->expr = $expr;
        $this->body = $body;
    }

    public function getExpr() {
        return $this->expr;
    }

    public function getChildren() {
        return $this->body->getChildren();
    }

}
