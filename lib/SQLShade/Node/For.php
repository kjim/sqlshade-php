<?php
require_once(dirname(__FILE__).'/../Node.php');
require_once(dirname(__FILE__).'/Compound.php');

class SQLShade_Node_For extends SQLShade_Node {

    protected $item;
    protected $ident;
    protected $body;

    public function __construct($item, $ident, SQLShade_Node_Compound $body, $lineno) {
        parent::__construct($lineno);

        $this->item = $item;
        $this->ident = $ident;
        $this->body = $body;
    }

    public function getChildren() {
        return $this->body->getChildren();
    }

}
