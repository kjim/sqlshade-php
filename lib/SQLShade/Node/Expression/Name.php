<?php
require_once(dirname(__FILE__).'/../Expression.php');

class SQLShade_Node_Expression_Name extends SQLShade_Node_Expression {

    protected $name;

    public function __construct($name, $lineno, $token) {
        parent::__construct($lineno, $token);
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

}
