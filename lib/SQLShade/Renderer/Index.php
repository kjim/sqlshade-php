<?php
require_once(dirname(__FILE__).'/../Printer/Index.php');

class SQLShade_Renderer_Index {

    protected $env;
    protected $strict;

    public function __construct($env) {
        $this->env = $env;
        $this->strict = true;
    }

    public function render(/*Node_Module*/$node) {
        $printer = new SQLShade_Printer_PHP();
        $this->printTemplate($node, $printer);

        $this->traverse($moduleNode->getBody(), $printer);
        return $printer->getSource();
    }

    protected function traverse($node, $printer) {
        foreach ($node->getChildren() as $n) {
            $n->acceptVisitor($this, $printer);
        }
    }

    public function visitLiteral($node, $printer) {
    }

    public function visitSubstitute($node, $printer) {
        $this->writeSubstitute($node, $printer);
    }

    protected function writeSubstitute($node, $printer) {
    }

    public function visitEmbed($node, $printer) {
    }

    public function visitEval($node, $printer) {
    }

    public function visitIf($node, $printer) {
    }

    public function visitFor($node, $printer) {
    }

}
