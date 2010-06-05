<?php
require_once(dirname(__FILE__).'/../Node.php');

class SQLShade_Node_Literal extends SQLShade_Node {

    protected $literal;

    public function __construct($literal, $lineno, $token = null) {
        parent::__construct($lineno, $token);

        $this->literal = $literal;
    }

    public function getLiteral() {
        return $this->literal;
    }

}
