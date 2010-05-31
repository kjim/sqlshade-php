<?php
require_once(dirname(__FILE__).'/../Node.php');

class SQLShade_Node_If extends SQLShade_Node {

    protected $ident;
    protected $body;

    public function __construct($ident, /*Node_Compound*/$body, $lineno) {
        parent::__construct($lineno);

        $this->ident = $ident;
        $this->body = $body;
    }

    public function getIdent() {
        return $this->ident;
    }

    public function getChildren() {
        return $this->body->getChildren();
    }

}
