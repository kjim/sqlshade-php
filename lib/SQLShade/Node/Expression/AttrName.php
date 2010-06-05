<?php

class SQLShade_Node_Expression_AttrName extends SQLShade_Node_Expression {

    protected $node;
    protected $attr;

    public function __construct($node, $attr, $lineno, $token = null) {
        parent::__construct($lineno, $token);

        $this->node = $node;
        $this->attr = $attr;
    }

    public function getNode() {
        return $this->node;
    }

    public function getAttr() {
        return $this->attr;
    }

}
