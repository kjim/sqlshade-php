<?php
require_once(dirname(__FILE__).'/../Node.php');

class SQLShade_Node_Substitute extends SQLShade_Node {

    protected $expr;
    protected $faketext;

    public function __construct($expr, $faketext, $lineno, $token) {
        parent::__construct($lineno, $token);

        $this->expr = $expr;
        $this->faketext = $faketext;
    }

    public function getExpr() {
        return $this->expr;
    }

    public function getFaketext() {
        return $this->faketext;
    }

}
