<?php

class SQLShade_Printer_Index {

    protected $buf;
    protected $bound;

    public function __construct() {
        $this->buf = '';
        $this->bound = array();
    }

    public function write($fragment) {
        $this->buf .= $fragment;
    }

    public function bind($variable) {
        $this->bound[] = $variable;
    }

    public function freeze() {
        return array(&$this->buf, &$this->bound);
    }

}
