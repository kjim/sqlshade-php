<?php
require_once(dirname(__FILE__).'/../Expression.php');

class SQLShade_Node_Expression_Name extends SQLShade_Node_Expression
{
    protected $name;

    public function __construct($name, $lineno)
    {
        parent::__construct($lineno);
        $this->name = $name;
    }

    public function __toString()
    {
        return get_class($this).'(' . $this->name . ')';
    }

    public function getName()
    {
        return $this->name;
    }
}
