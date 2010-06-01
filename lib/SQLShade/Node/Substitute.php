<?php
require_once(dirname(__FILE__).'/../Node.php');

class SQLShade_Node_Substitute extends SQLShade_Node {

    protected $ident;
    protected $faketext;

    public function __construct($ident, $faketext, $lineno) {
        parent::__construct($lineno);

        $this->ident = $ident;
        $this->faketext = $faketext;
    }

    public function getIdent() {
        return $this->ident;
    }

    public function getFaketext() {
        return $this->faketext;
    }

}
