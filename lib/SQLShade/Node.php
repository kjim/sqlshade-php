<?php

abstract class SQLShade_Node {

    protected
        $lineno,
        $tag
        ;

    public function __construct($lineno, $tag = null) {
        $this->lineno = $lineno;
        $this->tag = $tag;
    }

    public function __toString() {
        return get_class($this).'()';
    }

    public function getLine() {
        return $this->lineno;
    }

    public function getNodeTag() {
        return $this->tag;
    }

    public function getChildren() {
        return array();
    }

}
