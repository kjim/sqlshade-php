<?php
require_once(dirname(__FILE__).'/../Expression.php');

class SQLShade_Node_Expression_Constant extends SQLShade_Node_Expression
{
    protected $value;

    public function __construct($value, $lineno)
    {
        parent::__construct($lineno);
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
