<?php

abstract class SQLShade_Node
{
    protected $lineno;

    public function __construct($lineno)
    {
        $this->lineno = $lineno;
    }

    public function __toString()
    {
        return get_class($this).'()';
    }

    public function getLine()
    {
        return $this->lineno;
    }

    public function getChildren()
    {
        return array();
    }

    public function acceptVisitor($visitor, &$opts = null)
    {
        $method = $this->getVisitName();
        if (method_exists($visitor, $method)) {
            $visitor->$method($this, $opts);
        }
        else {
            $this->traverse($this, $visitor, $opts);
        }
    }

    protected function traverse($node, $visitor, &$opts = null)
    {
        foreach ($node->getChildren() as $n) {
            $n->acceptVisitor($visitor, $opts);
        }
    }

    protected function getVisitName()
    {
        return 'visit' . array_pop(explode('_', get_class($this)));
    }
}
