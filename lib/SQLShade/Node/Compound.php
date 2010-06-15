<?php
require_once(dirname(__FILE__).'/../Node.php');

class SQLShade_Node_Compound extends SQLShade_Node {

    protected $nodes;

    public function __construct(array $nodes, $lineno = 0) {
        parent::__construct($lineno);

        $this->nodes = $nodes;
    }

    public function __toString() {
        $repr = array(get_class($this), '(');
        $repr[] = '[';
        foreach ($this->nodes as $node) {
            $repr[] = $node->__toString();
        }
        $repr[] = ']';
        return implode('', $repr);
    }

    public function getChildren() {
        return $this->nodes;
    }

    public function appendNode(/*Node*/$node) {
        $this->nodes[] = $node;
    }

}
