<?php
require_once(dirname(__FILE__).'/../Expression.php');

class SQLShade_Node_Expression_Constant extends SQLShade_Node_Expression {

    protected $value;

    public function __construct($value, $lineno, $token = null) {
        parent::__construct($lineno, $token);
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

}
