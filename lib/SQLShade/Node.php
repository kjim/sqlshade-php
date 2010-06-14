<?php

abstract class SQLShade_Node {

    protected $lineno;
    protected $token;

    public function __construct($lineno, $token) {
        $this->lineno = $lineno;
        $this->token = $token;
    }

    public function __toString() {
        return get_class($this).'()';
    }

    public function getLine() {
        return $this->lineno;
    }

    public function getToken() {
        return $this->token;
    }

    public function getChildren() {
        return array();
    }

    public function acceptVisitor($visitor, &$opts = null) {
        $method = $this->getVisitName();
        if (method_exists($visitor, $method)) {
            $visitor->$method($this, $opts);
        }
        else {
            $this->traverse($this, $visitor, $opts);
        }
    }

    protected function traverse($node, $visitor, &$opts = null) {
        foreach ($node->getChildren() as $n) {
            $n->acceptVisitor($visitor, $opts);
        }
    }

    protected function getVisitName() {
        return 'visit' . array_pop(explode('_', get_class($this)));
    }

}
