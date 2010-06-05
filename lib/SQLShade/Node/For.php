<?php
require_once(dirname(__FILE__).'/../Node.php');

class SQLShade_Node_For extends SQLShade_Node {

    protected $item;
    protected $ident;
    protected $body;

    public function __construct($item, $ident, /*Node_Compound*/$body, $lineno, $token) {
        parent::__construct($lineno, $token);

        $this->item = $item;
        $this->ident = $ident;
        $this->body = $body;
    }

    public function getItem() {
        return $this->item;
    }

    public function getIdent() {
        return $this->ident;
    }

    public function getBody() {
        return $this->body;
    }

    public function getChildren() {
        return $this->body->getChildren();
    }

}
