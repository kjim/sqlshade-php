<?php
require_once(dirname(__FILE__).'/../Node.php');

class SQLShade_Node_Literal extends SQLShade_Node
{
    protected $literal;

    public function __construct($literal, $lineno)
    {
        parent::__construct($lineno);
        $this->literal = $literal;
    }

    public function getLiteral()
    {
        return $this->literal;
    }
}
