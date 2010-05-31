<?php
require_once(dirname(__FILE__).'/../Node.php');

class SQLShade_Node_Embed extends SQLShade_Node {

    protected $ident;

    public function __construct($ident, $lineno) {
        parent::__construct($lineno);

        $this->ident = $ident;
    }

}
